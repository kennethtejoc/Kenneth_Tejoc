# Kenneth Tejoc Personal Portfolio

A dynamic personal portfolio website built for the ITS 300-B final project. The public site uses PHP and MySQL, while authenticated administrators manage portfolio content through the dashboard.

## Technology

- PHP 8+
- MySQL or MariaDB
- PDO prepared statements
- HTML, CSS, and JavaScript
- Apache-compatible hosting such as TinkerHost

## Features

- Database-backed home, About, Skills, Projects, Resume, and Contact pages
- Admin login with `password_hash()`, `password_verify()`, PHP sessions, session-ID regeneration, CSRF tokens, and login-attempt throttling
- CRUD management for skills, projects, education, experience, and achievements
- Editable profile information and website settings
- Contact messages stored in MySQL with read/unread and delete controls
- Server-side input validation and HTML escaping
- Secure image uploads using content detection, allowed MIME types, a 5 MB size limit, random filenames, and a non-executable upload directory
- Responsive desktop and mobile layout
- Git history with progressive feature commits

## Local or hosting setup

1. Create a MySQL database and user.
2. Import `database.sql` into the database.
3. Copy `config.sample.php` to `config.php` on the server and set the database password privately. On this TinkerHost account, MySQL uses the hosting account's vPanel password. Never commit `config.php` or share the password.
4. Upload the repository files to the existing midterm website address.
5. Confirm that the site is served over HTTPS.
6. Open `/admin/`. On a fresh database, create the first administrator with a password of at least 12 characters.
7. Use the dashboard to update the profile, settings, skills, projects, education, experience, achievements, and messages.

## Image uploads

Use the Profile page to upload a profile image and the Projects manager to upload project images. The application accepts JPEG, PNG, GIF, and WebP files up to 5 MB. Uploaded files are saved under `assets/images/uploads/` with random names. The directory contains an Apache rule that blocks PHP execution.

## Deployment checklist

- `config.php` exists on the server but is not tracked by Git.
- `database.sql` has been imported and the initial data is present.
- PHP PDO MySQL support is enabled.
- `assets/images/uploads/` is writable by PHP but not executable.
- HTTPS is enabled for the public site and `/admin/`.
- Test admin login, failed-login throttling, CRUD changes, contact submission, message deletion, and image uploads after deployment.
- Keep the same URL used for the midterm project.

## Commit history

The implementation was intentionally committed in focused steps rather than one large commit:

1. `80a5822` — Admin content management, dashboard statistics, and settings
2. `da3176e` — Admin login throttling and message lifecycle management
3. `2176937` — Secure portfolio image uploads and dynamic image rendering
4. Final documentation and deployment hardening
