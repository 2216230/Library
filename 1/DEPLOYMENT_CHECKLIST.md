# Render Deployment Checklist

## ✅ Pre-Deployment (Completed)

- [x] Created Dockerfile for PHP 8.2 with Apache
- [x] Created render.yaml for Blueprint deployment
- [x] Created database initialization script (docker/init.sql)
- [x] Created entrypoint script for environment variable configuration
- [x] Pushed code to GitHub repository: https://github.com/secre159/bsu-bokod-lms.git

## 📋 Deployment Steps

### 1. Create Render Account
- Go to https://render.com/
- Sign up with GitHub (recommended for easy integration)

### 2. Deploy Using Blueprint

1. Click **"New +"** → **"Blueprint"**
2. Select your repository: `secre159/bsu-bokod-lms`
3. Render will detect `render.yaml` automatically
4. Click **"Apply"**

This will create:
- ✅ Web Service (bsu-bokod-lms)
- ✅ MySQL Database (bsu-bokod-db)

### 3. Configure Email Settings

After services are created, go to **Web Service** → **Environment**:

Add these variables:
```
MAIL_USERNAME = your-email@gmail.com
MAIL_PASSWORD = your-gmail-app-password
MAIL_FROM_ADDRESS = your-email@gmail.com
```

**Get Gmail App Password:**
1. Google Account → Security → 2-Step Verification (enable it)
2. Security → App passwords
3. Generate password for "Mail"
4. Copy the 16-character password

### 4. Initialize Database

**Option A: Using MySQL Client**
1. In Render Dashboard → Database → Connect
2. Copy connection details
3. Connect with MySQL Workbench or DBeaver
4. Run `docker/init.sql` script

**Option B: Using Command Line**
```bash
mysql -h [hostname] -u [username] -p[password] [database] < docker/init.sql
```

Get connection details from Render Dashboard → Database → Info tab

### 5. Verify Deployment

Wait 5-10 minutes for deployment to complete, then:

1. Visit your URL: `https://bsu-bokod-lms.onrender.com`
2. Login with default admin:
   - Email: `admin@bsu.edu.ph`
   - Password: `admin123`
3. **IMMEDIATELY change the password!**

## 🔍 Troubleshooting

### Database Connection Failed
- Check database service is running
- Verify environment variables are set
- Check logs for specific errors

### Service Won't Start
- Check **Logs** tab in Render dashboard
- Verify Docker build completed successfully
- Ensure database is initialized

### Email Not Working
- Verify Gmail App Password is correct
- Check 2-Step Verification is enabled
- Ensure all mail environment variables are set

## 📊 Monitoring

### Check Service Health
- Render Dashboard → Your Service → Logs
- Look for "Apache started successfully"
- Check for PHP errors or warnings

### Database Status
- Render Dashboard → Database → Metrics
- Monitor connections and storage usage

## 🔄 Updating Your Deployment

After making code changes:
```bash
git add .
git commit -m "Your changes"
git push origin main
```

Render will automatically rebuild and redeploy.

## 💰 Cost Information

**Free Tier:**
- Web Service: Free (spins down after 15 min inactivity)
- Database: First 90 days free, then ~$7/month

**Note:** On free tier, first request after inactivity takes 30-60 seconds.

## 🚀 Production Recommendations

1. **Upgrade to paid plan** for always-on service
2. **Add custom domain** (available on paid plans)
3. **Set up database backups** (in Render dashboard)
4. **Configure persistent storage** for uploads (S3, Cloudinary)
5. **Monitor logs** regularly
6. **Change all default passwords**

## 📝 Important Notes

- Default admin: `admin@bsu.edu.ph` / `admin123`
- Files uploaded in free tier may be lost on redeploy
- Keep email credentials secure
- Database URL changes require updating environment variables
- Check logs if you encounter issues

## 🆘 Need Help?

1. Read DEPLOY.md for detailed instructions
2. Check Render documentation: https://render.com/docs
3. Review application logs in Render dashboard
4. Check GitHub repository issues

---

**Repository:** https://github.com/secre159/bsu-bokod-lms
**Render Dashboard:** https://dashboard.render.com/
