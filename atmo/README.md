# Atmo Backend API Documentation

Welcome to the backend API for **Atmo**, a modern social media platform. This API is built using **CodeIgniter 4** and provides robust endpoints for authentication, social networking, and content management.

## 🚀 Getting Started

### Prerequisites
- PHP 8.1+
- MySQL/MariaDB
- Composer

### Installation
1. Clone the repository.
2. Run `composer install`.
3. Configure your `.env` file with database credentials.
4. Run migrations/import `atmo.sql` to your database.
5. Start the server:
   ```bash
   php spark serve
   ```

---

## 🔐 Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `POST` | `/api/auth/register` | Create a new user account | No |
| `POST` | `/api/auth/login` | Log in and start session | No |
| `POST` | `/api/auth/logout` | Log out and destroy session | Yes |
| `GET` | `/api/auth/me` | Get current logged-in user details | Yes |

### Registration Payload (Multipart/Form-Data)
- `username`, `email`, `password`, `first_name`, `last_name`, `dob`, `sex`

---

## 📝 Post & Feed Endpoints

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `GET` | `/api/posts` | Fetch personalized news feed | Yes |
| `POST` | `/api/posts` | Create a new post (text/media) | Yes |
| `GET` | `/api/posts/{id}` | View single post details | Yes |
| `DELETE` | `/api/posts/{id}` | Delete your own post | Yes |

### Create Post Payload (Multipart/Form-Data)
- `content`: (String) Text content.
- `visibility`: `public`, `followers`, or `private`.
- `media`: (File) Optional Image or Video.

---

## ❤️ Social & Interaction Endpoints

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `POST` | `/api/posts/{id}/like` | Toggle Like/Unlike on a post | Yes |
| `POST` | `/api/posts/{id}/comment` | Add a comment to a post | Yes |
| `POST` | `/api/posts/{id}/repost` | Repost content with optional quote | Yes |

### Comment Payload
- `comment_text`: (String)

---

## 👥 User & Network Endpoints

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `GET` | `/api/users/search?q=...` | Search users by name/email | No |
| `GET` | `/api/users/{username}` | View user profile and their posts | No |
| `POST` | `/api/users/update` | Update bio or profile picture | Yes |
| `POST` | `/api/users/{id}/follow` | Follow/Unfollow a user | Yes |
| `POST` | `/api/users/{id}/block` | Block/Unblock a user | Yes |
| `GET` | `/api/users/{id}/stats` | Get follower/following counts | Yes |

---

## 🛡️ Security Features
- **Bcrypt Hashing**: Secure password storage.
- **CSRF Ready**: Framework-level protection available.
- **SQLi Protection**: All queries utilize CI4 Models with prepared statements.
- **Access Control**: Middleware (Filters) protects sensitive endpoints.
- **Ownership Validation**: Users can only modify/delete their own data.

## 📂 Uploads Path
- **Posts**: `/public/uploads/posts/`
- **Profiles**: `/public/uploads/profiles/`

---
*Built with ❤️ for the Atmo Project.*
