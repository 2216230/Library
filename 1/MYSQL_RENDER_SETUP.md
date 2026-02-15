# MySQL Deployment on Render

## 🎯 Overview

You'll deploy with **MySQL on Render** (paid tier). Here's the process:

---

## 💰 Cost

- **Web Service**: Free tier available
- **MySQL Database**: Starts at **$7/month** for 1GB RAM
- **Total**: ~$7/month for always-on service with MySQL

---

## 🚀 Deployment Steps

### Step 1: Create Web Service

1. Go to https://dashboard.render.com/
2. Click **"New +"** → **"Web Service"**
3. Connect your GitHub repo: `secre159/bsu-bokod-lms`
4. Configure:
   - **Name**: bsu-bokod-lms
   - **Runtime**: Docker
   - **Branch**: main
   - **Plan**: Free (or paid for always-on)

### Step 2: Create MySQL Database

1. In Render Dashboard, click **"New +"** → **"MySQL"**
2. Configure:
   - **Name**: bsu-bokod-db
   - **Database**: libsystem4
   - **User**: (auto-generated)
   - **Region**: Same as web service
   - **Plan**: Starter ($7/mo) or higher

3. **Wait for database to provision** (~2-3 minutes)

### Step 3: Connect Database to Web Service

1. Go to your **Web Service** → **Environment**
2. Click **"Add Environment Variable"**
3. Add these variables from your MySQL database info:

```
DB_HOST = [from MySQL dashboard - Internal Hostname]
DB_PORT = 3306
DB_USER = [from MySQL dashboard - Username]
DB_PASSWORD = [from MySQL dashboard - Password]
DB_NAME = libsystem4
```

**Where to find these values:**
- Go to MySQL service → **Connect** tab
- Use **Internal Database URL** for internal hostname
- Copy username and password

### Step 4: Add Email Configuration

Still in Environment section, add:
```
MAIL_HOST = smtp.gmail.com
MAIL_PORT = 587
MAIL_USERNAME = your-email@gmail.com
MAIL_PASSWORD = your-gmail-app-password
MAIL_FROM_ADDRESS = your-email@gmail.com
```

### Step 5: Deploy

1. Click **"Manual Deploy"** → **"Deploy latest commit"**
2. Wait for build to complete (~5-10 minutes)
3. Check logs for successful deployment

### Step 6: Initialize Database

**Option A: Using MySQL Workbench**
1. Download [MySQL Workbench](https://dev.mysql.com/downloads/workbench/)
2. Get **External Connection** details from Render MySQL dashboard
3. Connect to database
4. Run `docker/init.sql`

**Option B: Using Command Line**
```bash
mysql -h [external-hostname] -u [username] -p[password] libsystem4 < docker/init.sql
```

**Option C: Using phpMyAdmin**
1. Use any phpMyAdmin service
2. Connect with external connection details
3. Import `docker/init.sql`

---

## ✅ Verify Deployment

1. Visit your Render URL (e.g., `https://bsu-bokod-lms.onrender.com`)
2. Login with:
   - **Email**: admin@bsu.edu.ph
   - **Password**: admin123
3. **Change password immediately!**

---

## 🔧 Troubleshooting

### Database Connection Failed

Check these:
1. ✅ Database is running (check Render dashboard)
2. ✅ Environment variables are set correctly
3. ✅ Using **Internal Hostname** (not external)
4. ✅ Database is in the same region as web service
5. ✅ Database has been initialized with schema

### Can't Connect from Outside

- External connections may take a few minutes to activate
- Check **Connect** tab for correct external hostname
- Verify port 3306 is allowed

### Service Won't Start

1. Check **Logs** tab for errors
2. Verify all environment variables are set
3. Ensure Dockerfile builds successfully
4. Check database connection string

---

## 📊 Getting Database Connection Details

### For Web Service (Use Internal):
1. MySQL Dashboard → **Info** tab
2. Copy **Internal Database URL**
3. Format: `mysql://user:pass@internal-host:3306/dbname`
4. Parse this into individual environment variables

### For External Tools (Use External):
1. MySQL Dashboard → **Connect** tab  
2. Use **External Connection** details
3. Hostname will be different from internal

---

## 🔄 Alternative: External MySQL Provider

If Render MySQL is too expensive, consider:

### Option 1: PlanetScale (Recommended)
- **Cost**: Free 5GB, then $29/month
- **Benefits**: Serverless, better performance
- **Setup**: https://planetscale.com/

### Option 2: Railway
- **Cost**: $5/month
- **Benefits**: Simpler pricing
- **Setup**: https://railway.app/

### Option 3: AWS RDS
- **Cost**: ~$15-30/month
- **Benefits**: Production-grade
- **Setup**: https://aws.amazon.com/rds/

For any external provider:
1. Create MySQL database there
2. Get connection details
3. Add to Render as environment variables
4. No need for Render MySQL service

---

## 💡 Cost Optimization Tips

1. **Free Web Service** + **External MySQL** = ~$5-7/month
2. Use Render paid plan only if you need always-on
3. Start with free web service, upgrade if needed
4. Consider PlanetScale's free tier for development

---

## 📝 Important Notes

- Use **Internal Hostname** for web service connection
- Use **External Hostname** for admin tools
- Database takes 2-3 minutes to provision
- Environment variables require manual deployment after changes
- Keep credentials secure - never commit to git

---

## 🎊 Quick Checklist

- [ ] Create Web Service on Render
- [ ] Create MySQL Database on Render
- [ ] Add DB_* environment variables to Web Service
- [ ] Add MAIL_* environment variables
- [ ] Deploy Web Service
- [ ] Initialize database with init.sql
- [ ] Test login
- [ ] Change default password

---

## 🆘 Need Help?

- Check Render logs: Dashboard → Service → Logs
- Check database status: Dashboard → MySQL → Metrics
- See connection details: Dashboard → MySQL → Connect
- Read official docs: https://render.com/docs/databases

---

**Ready to deploy!** Follow the steps above and you'll have MySQL running in 15 minutes.
