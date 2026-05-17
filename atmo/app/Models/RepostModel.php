<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * RepostModel - Manages repost relationships
 * 
 * A repost is when a user shares another user's post.
 * This model handles:
 * - Creating new repost records
 * - Deleting reposts (permanent removal from database)
 * - Querying repost data with relationships
 * 
 * @package App\Models
 */
class RepostModel extends Model
{
    protected $table            = 'reposts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Hard delete for reposts - no soft delete
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'post_id', 'repost_text'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'post_id' => 'required|integer',
        'user_id' => 'required|integer'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
