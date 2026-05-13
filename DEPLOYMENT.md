# Render + GitHub + MySQL Deployment Guide

This project is ready to deploy to Render as a Docker web service. Render will host the PHP app, while MySQL should be hosted by an external MySQL provider because Render's managed database product is PostgreSQL.

## 1. Prepare GitHub

Create a new GitHub repository, then push this project:

```powershell
git add .
git commit -m "Prepare Render deployment"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
git push -u origin main
```

Do not commit `.env`. It is ignored by `.gitignore`.

## 2. Create an Online MySQL Database

Use an external hosted MySQL service such as Aiven, Railway, Clever Cloud, AlwaysData, or another MySQL provider.

Create a database, then copy these values:

- Host
- Port, usually `3306`
- Database name
- Username
- Password

Do not use `localhost`, `127.0.0.1`, or `root` on Render. Those only work on your XAMPP computer.

## 3. Import SQL Using MySQL Workbench

1. Open MySQL Workbench.
2. Click `+` beside MySQL Connections.
3. Fill in your hosted MySQL details:
   - Hostname: your online MySQL host
   - Port: `3306`
   - Username: your online MySQL username
   - Password: store in vault or enter when prompted
4. Click `Test Connection`.
5. Open [database/schema.sql](database/schema.sql).
6. Run the full script.
7. Open [database/seed.sql](database/seed.sql).
8. Run the full script.

The seeded admin account is:

```text
schoolyoro@gmail.com / Testing!1
```

## 4. Create the Render Web Service

Option A: Blueprint

1. Push `render.yaml` to GitHub.
2. In Render, choose `New > Blueprint`.
3. Select your GitHub repository.
4. Render will read [render.yaml](render.yaml).
5. Fill the environment variables marked as secret/manual.

Option B: Manual Web Service

1. In Render, choose `New > Web Service`.
2. Connect your GitHub repository.
3. Set runtime/language to `Docker`.
4. Render will use [Dockerfile](Dockerfile).
5. Set health check path:

```text
/health.php
```

## 5. Render Environment Variables

In Render, go to your service's `Environment` tab and add:

```env
APP_ENV=production
APP_NAME=Lost and Found Management System
APP_URL=https://YOUR-RENDER-SERVICE.onrender.com

DB_HOST=your-online-mysql-host
DB_PORT=3306
DB_NAME=your-online-database-name
DB_USER=your-online-mysql-user
DB_PASS=your-online-mysql-password
```

Alternatively, if your MySQL provider gives a full URL, you can use:

```env
DATABASE_URL=mysql://username:password@host:3306/database_name
```

If `DATABASE_URL` is set, it overrides the individual `DB_*` variables.

## 6. PHPMailer Gmail SMTP on Render

Add these Render environment variables:

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=schoolyoro@gmail.com
MAIL_PASSWORD=your-16-character-gmail-app-password
MAIL_FROM_ADDRESS=schoolyoro@gmail.com
MAIL_FROM_NAME=Lost and Found Office
```

Important:

- Use a Gmail App Password, not your normal Gmail password.
- Paste the app password without spaces.
- The Gmail account in `MAIL_USERNAME` should be the same account that generated the app password.
- If Render logs show `SMTP Error: Could not authenticate`, generate a fresh app password in Google Account Security and update `MAIL_PASSWORD`.

## 7. Cloudinary on Render

Add these Render environment variables:

```env
CLOUDINARY_CLOUD_NAME=your-cloud-name
CLOUDINARY_API_KEY=your-api-key
CLOUDINARY_API_SECRET=your-api-secret
CLOUDINARY_FOLDER=school-lost-found/items
```

Images are uploaded to Cloudinary, so Render does not need persistent disk storage for item uploads.

## 8. Deploy

After environment variables are set:

1. Click `Manual Deploy > Deploy latest commit`, or push a new commit to GitHub.
2. Wait for Render to build the Docker image.
3. Open your Render URL.
4. Log in with:

```text
schoolyoro@gmail.com / Testing!1
```

5. Check your email for the OTP.

## 9. Troubleshooting

Database connection fails:

- Make sure the online MySQL provider allows external connections.
- Make sure the host is not `localhost`.
- Confirm database name, username, and password.
- Import `schema.sql` before `seed.sql`.

Email fails:

- Check the `email_logs` table.
- Confirm Gmail app password is valid.
- Confirm `MAIL_USERNAME` and `MAIL_FROM_ADDRESS` are the same Gmail address.
- Paste the app password without spaces.

Images fail:

- Check Cloudinary credentials.
- Upload JPG, PNG, or WEBP only.
- Keep images under 5 MB.
