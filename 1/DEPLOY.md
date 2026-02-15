# Deployment Guide for Render

This guide will help you deploy the BSU-Bokod Library Management System to Render.

## Prerequisites

1. A [Render account](https://render.com/) (free tier works)
2. Git repository with your code pushed to GitHub, GitLab, or Bitbucket
3. Gmail account with App Password for email functionality

## Step 1: Push Code to Git Repository

Make sure all deployment files are committed and pushed to your repository:

```powershell
git add .
git commit -m "Add Render deployment configuration"
git push origin main
```

## Step 2: Create a New Web Service on Render

1. Go to [Render Dashboard](https://dashboard.render.com/)
2. Click **"New +"** → **"Blueprint"**
3. Connect your Git repository
4. Render will automatically detect the `render.yaml` file

## Step 3: Configure Environment Variables

After the blueprint is created, you need to set the email configuration in the Render dashboard:

### For the Web Service:

1. Go to your web service in the Render dashboard
2. Navigate to **Environment** section
3. Add the following environment variables:

| Variable Name | Value | Description |
|---------------|-------|-------------|
| `MAIL_USERNAME` | your-email@gmail.com | Your Gmail address |
| `MAIL_PASSWORD` | xxxx xxxx xxxx xxxx | Gmail App Password (see below) |
| `MAIL_FROM_ADDRESS` | your-email@gmail.com | Email address to receive contact form submissions |

### How to Get Gmail App Password:

1. Go to your Google Account settings
2. Navigate to **Security** → **2-Step Verification** (enable if not already)
3. Scroll down to **App passwords**
4. Select **Mail** and **Other (Custom name)**
5. Name it "BSU Library System"
6. Copy the generated 16-character password
7. Use this password (with spaces or without) for `MAIL_PASSWORD`

## Step 4: Initialize the Database

After deployment, you need to initialize the database with the schema:

### Option A: Using Render Shell (Recommended)

1. Go to your database in Render dashboard
2. Click on **"Connect"** and copy the **External Database URL**
3. Use a MySQL client (like MySQL Workbench, DBeaver, or command line) to connect
4. Run the SQL script from `docker/init.sql`

### Option B: Using MySQL Command Line

```bash
# Get the connection details from Render dashboard
mysql -h <hostname> -u <username> -p<password> <database_name> < docker/init.sql
```

## Step 5: Verify Deployment

1. Wait for both services (web and database) to be fully deployed (this may take 5-10 minutes)
2. Visit your Render URL (e.g., `https://bsu-bokod-lms.onrender.com`)
3. Try logging in with the default admin account:
   - **Email**: admin@bsu.edu.ph
   - **Password**: admin123

## Default Admin Credentials

After deployment, log in with:
- **Email**: `admin@bsu.edu.ph`
- **Password**: `admin123`

**⚠️ IMPORTANT**: Change the default password immediately after first login!

## Troubleshooting

### Database Connection Issues

If you see database connection errors:

1. Check that the database service is running in Render dashboard
2. Verify environment variables are set correctly
3. Check the logs in Render dashboard for specific error messages

### Service Won't Start

1. Check the **Logs** tab in Render dashboard
2. Common issues:
   - Missing environment variables
   - Database not initialized
   - Docker build errors

### Email Not Working

1. Verify `MAIL_USERNAME`, `MAIL_PASSWORD`, and `MAIL_FROM_ADDRESS` are set
2. Make sure you're using a Gmail App Password, not your regular password
3. Check that 2-Step Verification is enabled on your Google account

## Updating Your Deployment

To update your deployed application:

1. Make changes to your code locally
2. Commit and push to your Git repository:
   ```powershell
   git add .
   git commit -m "Your update message"
   git push origin main
   ```
3. Render will automatically detect the changes and redeploy

## Performance Notes

- **Free tier limitations**: 
  - Web service spins down after 15 minutes of inactivity
  - First request after inactivity may take 30-60 seconds
  - Database has limited storage and connections
  
- **For production use**, consider upgrading to a paid plan for:
  - Always-on service (no spin down)
  - More database storage
  - Better performance
  - Custom domain support

## File Uploads

The following directories are writable in the deployed application:
- `/libsystem/images/profile_user/` - User profile photos
- `/libsystem/e-books/` - E-book PDFs
- `/libsystem/admin/uploads/` - Admin file uploads

**Note**: Files uploaded to these directories will be stored in the container and may be lost on redeployment. For production, consider using cloud storage like AWS S3 or Cloudinary.

## Security Recommendations

1. **Change default admin password immediately**
2. **Use strong passwords** for all accounts
3. **Keep email credentials secure** - never commit them to Git
4. **Enable HTTPS** (Render provides this by default)
5. **Regularly backup your database** from the Render dashboard
6. **Monitor logs** for suspicious activity

## Support

If you encounter issues:
1. Check the Render [documentation](https://render.com/docs)
2. Review application logs in the Render dashboard
3. Check the database connection status
4. Verify all environment variables are set correctly

## Cost Estimate

**Free Tier** (includes):
- Web Service: Free (with limitations)
- PostgreSQL Database: 90 days free trial, then $7/month for 1GB
- Total: **Free** for 90 days, then **$7/month**

**Note**: Render offers a free trial for PostgreSQL but not MySQL on free tier. You may need to upgrade or use PostgreSQL instead if you want a truly free deployment.
