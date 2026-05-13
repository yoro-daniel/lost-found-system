# Lost and Found Management System

Modern PHP, MySQL, Bootstrap, and JavaScript web application for reporting, searching, claiming, and managing lost/found items.

## Features

- Authentication with admin and user roles.
- SMS OTP verification before login is completed.
- Lost item and found item reports with image uploads.
- Cloudinary image uploads with validated files and optimized delivery URLs.
- Smart search and filters by keyword, type, status, category, and location.
- Claim request workflow with admin approval/rejection.
- Admin dashboard with statistics, charts, activity logs, users, and reports.
- Toast notifications, client-side validation, responsive UI, and dark mode.
- Reusable Twilio SMS service for OTP and notifications.
- MVC-like folders: `Controllers`, `Models`, `Views`, `Services`, `Core`.

## Setup

1. Create a MySQL database by importing:

```sql
database/schema.sql
database/seed.sql
```

2. Copy `.env.example` to `.env` and update MySQL and Twilio values.
   Add Cloudinary `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, and `CLOUDINARY_API_SECRET` to enable item image uploads.

3. Install Composer dependencies:

```powershell
composer install
```

4. Run locally:

```powershell
php -S 127.0.0.1:8000 -t public
```

5. Open `http://127.0.0.1:8000`.

## Deployment

For Render, GitHub, MySQL Workbench, Twilio, and Cloudinary deployment steps, see [DEPLOYMENT.md](DEPLOYMENT.md).

## Demo Accounts

- Admin: `schoolyoro@gmail.com` / `Testing!1`
- User: `user@lostfound.test` / `user123`

## Twilio SMS Integration

The app uses `app/Services/SmsService.php` and Twilio Programmable Messaging.

SMS messages are attempted for:

- Login OTP verification.
- Report confirmations when a phone number is provided.
- Possible matching items when a phone number is provided.
- Claim approval or rejection decisions when the claimant phone is provided.

Failures are stored in the `sms_logs` table for admin review.

## Cloudinary Integration

The app uploads item images through `app/Services/CloudinaryUploadService.php`.

Required `.env` values:

```env
CLOUDINARY_CLOUD_NAME=your-cloud-name
CLOUDINARY_API_KEY=your-api-key
CLOUDINARY_API_SECRET=your-api-secret
CLOUDINARY_FOLDER=school-lost-found/items
```

Allowed uploads are JPG, PNG, and WEBP files up to 5 MB. The stored URL includes Cloudinary automatic format and quality optimization.
