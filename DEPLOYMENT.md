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

### Aiven MySQL Values

For your Aiven service, use these Render variables:

```env
DB_HOST=lostfound-db-lost-found-system.h.aivencloud.com
DB_PORT=19912
DB_NAME=defaultdb
DB_USER=avnadmin
DB_PASS=your-aiven-password
```

Aiven requires SSL. Add another Render variable named `DB_SSL_CA` and paste the full CA certificate value, including:

```text
-----BEGIN CERTIFICATE-----
...
-----END CERTIFICATE-----
```

Do not add quotation marks around the certificate in Render. Paste it as a multi-line value.

You may also use `DATABASE_URL` instead of the separate `DB_*` variables:

```env
DATABASE_URL=mysql://avnadmin:your-aiven-password@lostfound-db-lost-found-system.h.aivencloud.com:19912/defaultdb
```

Still add `DB_SSL_CA` because the app needs the Aiven CA certificate for SSL.

## 6. Twilio SMS on Render

### Create a Twilio account

1. Go to `https://www.twilio.com/try-twilio`.
2. Create an account and verify your email.
3. Verify your personal phone number.
4. In Twilio Console, open `Messaging`.
5. Try SMS Messaging and get a Twilio trial phone number.
6. Copy these values from Twilio Console:
   - Account SID
   - Auth Token
   - Twilio phone number

For trial accounts, Twilio only sends SMS to verified recipient phone numbers. Add your phone in Twilio Console under verified caller IDs or verified recipients before testing OTP.

Add these Render environment variables:

```env
TWILIO_ACCOUNT_SID=your-account-sid
TWILIO_AUTH_TOKEN=your-auth-token
TWILIO_FROM_NUMBER=+15551234567
TWILIO_MESSAGING_SERVICE_SID=
OTP_FALLBACK_PHONE=+639171234567
TWILIO_TIMEOUT=12
```

Important:

- Use E.164 phone number format, including the plus sign and country code.
- On a Twilio trial account, the OTP recipient must be a verified recipient number in Twilio.
- `OTP_FALLBACK_PHONE` is used for seeded/demo users that do not yet have a phone number in the database.
- If you use a Twilio Messaging Service, set `TWILIO_MESSAGING_SERVICE_SID`; otherwise leave it blank and use `TWILIO_FROM_NUMBER`.

### Update your existing Aiven database for Twilio

If your Aiven database was already created before Twilio was added, run this file once in MySQL Workbench:

```text
database/migrations/2026_05_13_twilio_sms.sql
```

Then set your admin phone number:

```sql
USE defaultdb;

UPDATE users
SET phone = '+639171234567'
WHERE email = 'schoolyoro@gmail.com';
```

Use your real verified Twilio recipient phone number in E.164 format.

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

5. Check your phone for the OTP SMS.

## 9. Troubleshooting

Database connection fails:

- Make sure the online MySQL provider allows external connections.
- Make sure the host is not `localhost`.
- Confirm database name, username, and password.
- Import `schema.sql` before `seed.sql`.

SMS fails:

- Check the `sms_logs` table.
- Confirm Twilio Account SID and Auth Token are correct.
- Confirm your Twilio sender number is SMS-capable.
- On a Twilio trial account, confirm the recipient phone number is verified.

Images fail:

- Check Cloudinary credentials.
- Upload JPG, PNG, or WEBP only.
- Keep images under 5 MB.
