# Atmo Backend API Documentation

Welcome to the backend API for **Atmo**, a modern social media platform. This API is built using **CodeIgniter 4** and provides robust endpoints for authentication, social networking, and content management.

##  Getting Started

### Prerequisites
- PHP 8.1+
- MySQL/MariaDB
- Composer

### Installation
1. Clone the repository.
2. Run `composer install`.
3. Configure your `.env` file with database credentials and JWT settings.
4. Run migrations/import `atmo.sql` to your database.
5. Start the server:
   ```bash
   php spark serve
   ```

---

## 🔐 Authentication & Security

This API uses **Stateless JWT (JSON Web Tokens)** for authentication.

### How to Authenticate:
1.  **Login**: Call `POST /api/auth/login`. On success, you will receive a `token`.
2.  **Authorize**: For all "Auth Required" endpoints, include the following header:
    *   **Key**: `Authorization`
    *   **Value**: `Bearer <your_token_here>`

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `POST` | `/api/auth/register` | Create a new user account | No |
| `POST` | `/api/auth/login` | Log in and receive JWT token | No |
| `GET` | `/api/auth/me` | Get current token's user details | Yes |

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

## 🛡️ Security Implementation
- **JWT Stateless Auth**: Secure, scalable token-based access.
- **Bcrypt Hashing**: Industry-standard password storage.
- **SQLi Protection**: Prepared statements via CI4 Query Builder.
- **Access Control**: Authorization levels enforced via Filters.
- **File Security**: Strict extension checks and randomized filenames.

## 📂 Uploads Path
- **Posts**: `/public/uploads/posts/`
- **Profiles**: `/public/uploads/profiles/`

---
*Built for the Atmo Project.*
