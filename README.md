# PHP-based Social Album Website

A PHP-based social media web application for picture album management and sharing.

---

## 📌 Project Overview

This project demonstrates the integration of PHP, MySQL, and front-end technologies to build a secure, database-driven social networking site for managing and sharing photo albums among friends.

---

## 🎯 Objectives

* Build a multi-page, authentication-protected PHP application.
* Implement secure user login, album management, and social friend interactions.
* Ensure the application is protected against SQL injection and password theft.

---

## 🧩 Core Features

* **User Authentication:** Secure sign-up and login with hashed passwords.
* **Album Management:** Create, edit, and delete albums with privacy settings (private/shared).
* **Picture Uploading:** Upload and manage pictures within albums.
* **Comment System:** Users can leave comments on pictures.
* **Friend System:** Add, accept, or deny friend requests, and view shared albums.
* **Access Control:** Private and shared visibility controlled through database relationships.

---

## 🗄️ Database Schema

Database: `cst8257project`
Tables include:

* `User`
* `Album`
* `Picture`
* `Comment`
* `Accessibility`
* `Friendship`
* `FriendshipStatus`

SQL script: `cst8257projectDB_Builder.sql` (provided)

---

## ⚙️ Technologies Used

* **Backend:** PHP 8+, MySQL
* **Frontend:** HTML5, CSS3, JavaScript
* **Database Tool:** MySQL Workbench/DataGrip
* **Security:** Password hashing, input validation, and SQL injection prevention

---

## 🚀 Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/zhu00188/PHP-based-PhotoShareNetwork.git
   ```
2. Import `cst8257projectDB_Builder.sql` in MySQL Workbench/DataGrip.
3. Configure database credentials in `config.php`.
4. Start your local server (XAMPP/WAMP).
5. Access the project at:

   ```
   http://localhost/PHP-based-PhotoShareNetwork/
   ```

---

## 👥 Pages Overview

| Page                 | Description                  |
| -------------------- | ---------------------------- |
| `index.php`          | Landing page                 |
| `newUser.php`        | User registration            |
| `login.php`          | User login                   |
| `myAlbums.php`       | View and manage albums       |
| `uploadPictures.php` | Upload photos                |
| `myPictures.php`     | Browse and comment on photos |
| `addFriend.php`      | Send friend requests         |
| `myFriends.php`      | Manage friends and requests  |
| `friendPictures.php` | View friends’ shared albums  |
