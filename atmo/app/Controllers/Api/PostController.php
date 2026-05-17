<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\PostModel;
use App\Models\LikeModel;
use App\Models\CommentModel;
use App\Models\RepostModel;
use App\Services\NotificationService;
use CodeIgniter\API\ResponseTrait;

/**
 * Api PostController - Handles REST API endpoints for posts
 * 
 * Provides JSON responses for:
 * - Feed data (For You, Your Atmosphere)
 * - Post interactions (likes, reposts, comments)
 * - Trending posts based on engagement
 * 
 * @package App\Controllers\Api
 */
class PostController extends BaseController
{
    use ResponseTrait;

    public function index($feedType = 'for_you')
    {
        $userId = session()->get('user_id');
        $db = \Config\Database::connect();
        
        $followingIdsQuery = $db->table('follows')
                               ->select('followed_id')
                               ->where('follower_id', $userId)
                               ->get()
                               ->getResultArray();
                               
        $followingIds = array_column($followingIdsQuery, 'followed_id');
        $followingIds[] = $userId; // Include self

        $postModel = new PostModel();
        $likeModel = new LikeModel();
        $commentModel = new CommentModel();
        $repostModel = new RepostModel();
        $userModel = new \App\Models\UserModel();
        
        // Helper function to add social data to a post
        $addSocialData = function(&$post) use ($likeModel, $commentModel, $repostModel, $userId, $userModel) {
            $postId = $post['id'];
            $post['like_count'] = $likeModel->where('post_id', $postId)->countAllResults();
            $post['comment_count'] = $commentModel->where('post_id', $postId)->countAllResults();
            $post['repost_count'] = $repostModel->where('post_id', $postId)->countAllResults();
            $post['is_liked'] = $likeModel->where('user_id', $userId)->where('post_id', $postId)->first() ? true : false;
            $post['is_reposted'] = $repostModel->where('user_id', $userId)->where('post_id', $postId)->first() ? true : false;
            
            // Fetch comments with user data
            $comments = $commentModel->where('post_id', $postId)
                                     ->orderBy('created_at', 'ASC')
                                     ->limit(10)
                                     ->findAll();
            foreach ($comments as &$comment) {
                $commentUser = $userModel->find($comment['user_id']);
                if ($commentUser) {
                    unset($commentUser['password']);
                    $comment['user'] = $commentUser;
                }
            }
            $post['comments'] = $comments;
        };
        
        // Fetch original posts
        if ($feedType === 'your_atmosphere') {
            // Your Atmosphere: only posts from followed users (including self)
            $posts = $postModel->whereIn('user_id', $followingIds)
                               ->where('visibility !=', 'private')
                               ->orderBy('created_at', 'DESC')
                               ->limit(50)
                               ->findAll();
                               
            $reposts = $repostModel->whereIn('user_id', $followingIds)
                                   ->orderBy('created_at', 'DESC')
                                   ->limit(50)
                                   ->findAll();
        } else {
            // For You: all public posts (or from followed if you have any)
            if (count($followingIds) > 1) {
                $posts = $postModel->whereIn('user_id', $followingIds)
                                   ->where('visibility !=', 'private')
                                   ->orderBy('created_at', 'DESC')
                                   ->limit(50)
                                   ->findAll();
                                   
                $reposts = $repostModel->whereIn('user_id', $followingIds)
                                       ->orderBy('created_at', 'DESC')
                                       ->limit(50)
                                       ->findAll();
            } else {
                $posts = $postModel->where('visibility', 'public')
                                   ->orderBy('created_at', 'DESC')
                                   ->limit(50)
                                   ->findAll();
                                   
                $reposts = $repostModel->where('user_id', $userId)
                                       ->orderBy('created_at', 'DESC')
                                       ->limit(50)
                                       ->findAll();
            }
        }

        $userModel = new \App\Models\UserModel();
        
        // Process original posts
        foreach ($posts as &$post) {
            $post['type'] = 'original';
            $user = $userModel->find($post['user_id']);
            if ($user) {
                unset($user['password']);
                $post['user'] = $user;
            }
            $addSocialData($post);
        }

        // Process reposts
        foreach ($reposts as &$repost) {
            $repost['type'] = 'repost';
            $author = $userModel->find($repost['user_id']);
            if ($author) {
                unset($author['password']);
                $repost['reposted_by'] = $author;
            }
            
            // Fetch original post details
            $originalPost = $postModel->find($repost['post_id']);
            if ($originalPost) {
                $originalAuthor = $userModel->find($originalPost['user_id']);
                if ($originalAuthor) {
                    unset($originalAuthor['password']);
                    $originalPost['user'] = $originalAuthor;
                }
                $addSocialData($originalPost);
                $repost['original_post'] = $originalPost;
            }
        }

        // Merge and sort
        $feed = array_merge($posts, $reposts);
        usort($feed, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $this->respond(array_slice($feed, 0, 50));
    }

    public function create()
    {
        $userId = session()->get('user_id');
        
        $rules = [
            'content'    => 'permit_empty|string',
            'visibility' => 'required|in_list[public,followers,private]',
        ];

        $file = $this->request->getFile('media');
        if ($file && $file->isValid()) {
            $rules['media'] = 'max_size[media,10240]|ext_in[media,png,jpg,jpeg,gif,mp4]';
        }

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $mediaPath = null;
        $mediaType = 'text';

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/posts', $newName);
            $mediaPath = 'uploads/posts/' . $newName;
            
            $mime = $file->getMimeType();
            $mediaType = strpos($mime, 'video') !== false ? 'video' : 'image';
        }

        $content = $this->request->getVar('content');
        if (empty($content) && empty($mediaPath)) {
            return $this->fail('Post cannot be completely empty.');
        }

        $postModel = new PostModel();

        $data = [
            'user_id'    => $userId,
            'content'    => $content,
            'media_path' => $mediaPath,
            'media_type' => $mediaType,
            'visibility' => $this->request->getVar('visibility'),
        ];

        if ($postModel->skipValidation(true)->insert($data)) {
            $data['id'] = $postModel->getInsertID();
            return $this->respondCreated(['status' => 'success', 'post' => $data]);
        }

        return $this->fail('Failed to create post');
    }

    public function show($id)
    {
        $postModel = new PostModel();
        $post = $postModel->find($id);

        if (!$post) {
            return $this->failNotFound('Post not found');
        }

        return $this->respond($post);
    }

    public function delete($id)
    {
        $userId = session()->get('user_id');
        $postModel = new PostModel();
        
        $post = $postModel->find($id);
        if (!$post) {
            return $this->failNotFound('Post not found');
        }

        if ($post['user_id'] != $userId) {
            return $this->failForbidden('You can only delete your own posts');
        }

        if ($postModel->delete($id)) {
            return $this->respondDeleted(['status' => 'success', 'message' => 'Post deleted']);
        }

        return $this->fail('Failed to delete post');
    }

    public function toggleLike($postId)
    {
        $userId = session()->get('user_id');
        $likeModel = new LikeModel();
        $postModel = new PostModel();
        
        $existing = $likeModel->where('user_id', $userId)->where('post_id', $postId)->first();

        if ($existing) {
            $likeModel->where('user_id', $userId)->where('post_id', $postId)->delete();
            $isLiked = false;
        } else {
            $likeModel->insert(['user_id' => $userId, 'post_id' => $postId]);
            $isLiked = true;
            
            $post = $postModel->find($postId);
            if ($post) {
                NotificationService::notify($post['user_id'], $userId, 'like', $postId);
            }
        }

        $likeCount = $likeModel->where('post_id', $postId)->countAllResults();
        
        return $this->respond([
            'status' => 'success',
            'is_liked' => $isLiked,
            'like_count' => $likeCount
        ]);
    }

    public function toggleRepost($postId)
    {
        $userId = session()->get('user_id');
        $postModel = new PostModel();
        $repostModel = new RepostModel();
        
        $originalPost = $postModel->find($postId);
        if (!$originalPost) {
            return $this->failNotFound('Post not found');
        }

        $existing = $repostModel->where('user_id', $userId)->where('post_id', $postId)->first();

        if ($existing) {
            $repostModel->where('user_id', $userId)->where('post_id', $postId)->delete();
            $isReposted = false;
        } else {
            $repostModel->insert(['user_id' => $userId, 'post_id' => $postId]);
            $isReposted = true;
            
            NotificationService::notify($originalPost['user_id'], $userId, 'repost', $postId);
        }

        $repostCount = $repostModel->where('post_id', $postId)->countAllResults();
        
        return $this->respond([
            'status' => 'success',
            'is_reposted' => $isReposted,
            'repost_count' => $repostCount
        ]);
    }

    public function addComment($postId)
    {
        $userId = session()->get('user_id');
        $text = $this->request->getPost('comment_text');

        if (empty($text)) {
            return $this->fail('Comment text cannot be empty');
        }

        $postModel = new PostModel();
        $post = $postModel->find($postId);
        if (!$post) {
            return $this->failNotFound('Post not found');
        }

        $commentModel = new CommentModel();
        $commentId = $commentModel->insert([
            'post_id' => $postId,
            'user_id' => $userId,
            'comment_text' => $text
        ]);

        $comment = $commentModel->find($commentId);

        $userModel = new \App\Models\UserModel();
        $commentUser = $userModel->find($userId);
        if ($commentUser) {
            unset($commentUser['password']);
            $comment['user'] = $commentUser;
        }

        $commentCount = $commentModel->where('post_id', $postId)->countAllResults();
        
        NotificationService::notify($post['user_id'], $userId, 'comment', $postId);

        return $this->respond([
            'status' => 'success',
            'comment' => $comment,
            'comment_count' => $commentCount
        ]);
    }

    /**
     * Get trending posts based on real interaction metrics
     * 
     * Trending algorithm prioritizes:
     * - Total interactions (likes + comments + reposts)
     * - Recent engagement (last 7 days)
     * - Engagement rate (interactions per post)
     * 
     * @return JSON response with trending posts
     */
    public function trending()
    {
        $db = \Config\Database::connect();
        
        // Get posts with high interaction counts from the last 7 days
        $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
        
        try {
            // Query to get trending posts with calculated interaction scores.
            // Group by all selected non-aggregated columns to support strict SQL modes.
            // This query is intentionally broad: it counts all reactions, comments, and reposts
            // for public posts, then ranks them by score and most recent activity.
            $trendingQuery = $db->query("
                SELECT 
                    p.id,
                    p.content,
                    p.user_id,
                    p.created_at,
                    u.first_name,
                    u.last_name,
                    u.username,
                    COUNT(DISTINCT l.user_id) as like_count,
                    COUNT(DISTINCT c.id) as comment_count,
                    COUNT(DISTINCT r.id) as repost_count,
                    (COUNT(DISTINCT l.user_id) + COUNT(DISTINCT c.id) * 2 + COUNT(DISTINCT r.id) * 3) as interaction_score,
                    GREATEST(
                        p.created_at,
                        COALESCE(MAX(c.created_at), p.created_at),
                        COALESCE(MAX(r.created_at), p.created_at)
                    ) as last_activity
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN likes l ON p.id = l.post_id
                LEFT JOIN comments c ON p.id = c.post_id
                LEFT JOIN reposts r ON p.id = r.post_id
                WHERE p.visibility = 'public'
                GROUP BY p.id, p.content, p.user_id, p.created_at, u.first_name, u.last_name, u.username
                HAVING interaction_score > 0
                ORDER BY interaction_score DESC, last_activity DESC
                LIMIT 7
            ");
            
            $trendingPosts = $trendingQuery->getResultArray();
        } catch (\Exception $ex) {
            log_message('error', 'Trending query failed: ' . $ex->getMessage());
            return $this->respond([]);
        }
        
        // If no trending posts, return recent popular posts
        if (empty($trendingPosts)) {
            try {
                $trendingQuery = $db->query("
                    SELECT 
                        p.id,
                        p.content,
                        p.user_id,
                        p.created_at,
                        u.first_name,
                        u.last_name,
                        u.username,
                        COUNT(DISTINCT l.id) as like_count,
                        COUNT(DISTINCT c.id) as comment_count,
                        COUNT(DISTINCT r.id) as repost_count
                    FROM posts p
                    LEFT JOIN users u ON p.user_id = u.id
                    LEFT JOIN likes l ON p.id = l.post_id
                    LEFT JOIN comments c ON p.id = c.post_id
                    LEFT JOIN reposts r ON p.id = r.post_id
                    WHERE p.visibility = 'public'
                    GROUP BY p.id, p.content, p.user_id, p.created_at, u.first_name, u.last_name, u.username
                    ORDER BY p.created_at DESC
                    LIMIT 5
                ");
                $trendingPosts = $trendingQuery->getResultArray();
            } catch (\Exception $ex) {
                log_message('error', 'Trending fallback query failed: ' . $ex->getMessage());
                return $this->respond([]);
            }
        }
        
        // Format response
        $trending = array_map(function($post) {
            return [
                'id' => $post['id'],
                'category' => 'Trending',
                'topic' => mb_strimwidth($post['content'], 0, 50, '...'),
                'post_count' => ($post['like_count'] + $post['comment_count'] + $post['repost_count']),
                'author' => $post['first_name'] . ' ' . $post['last_name'],
                'username' => $post['username'],
                'engagement' => $post['like_count'] . ' likes, ' . $post['comment_count'] . ' comments, ' . $post['repost_count'] . ' reposts'
            ];
        }, $trendingPosts);

        return $this->respond($trending);
    }
}
