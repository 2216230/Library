# Deploy MySQL on Render - Complete Guide

## 🎯 Overview

Render supports MySQL through **Private Services** using Docker. This guide shows you two ways to deploy.

---

## 🚀 Method 1: Manual Setup (Recommended)

### Step 1: Create MySQL Private Service

1. Go to https://dashboard.render.com/
2. Click **"New +"** → **"Private Service"**
3. Click **"Public Git repository"**
4. Enter: `https://github.com/render-examples/mysql`
5. Click **"Continue"**

**Configure the service:**
- **Name**: `bsu-bokod-mysql`
- **Region**: Choose closest to you
- **Branch**: `master` (for MySQL 8) or `mysql-5` (for MySQL 5)
- **Language**: Docker (auto-detected)
- **Plan**: Starter ($7/month)

### Step 2: Add Environment Variables

Click **"Add Environment Variable"** for each:

| Key | Value |
|-----|-------|
| `MYSQL_DATABASE` | `libsystem4` |
| `MYSQL_USER` | `libsystem_user` |
| `MYSQL_PASSWORD` | Generate secure password |
| `MYSQL_ROOT_PASSWORD` | Generate secure password |

**Save these passwords!** You'll need them later.

### Step 3: Add Disk

Scroll down to **"Disk"** section:
- **Name**: `mysql-data`
- **Mount Path**: `/var/lib/mysql` (MUST be exact)
- **Size**: `10 GB` (or more if needed)

Click **"Create Private Service"**

⏱️ Wait 3-5 minutes for MySQL to start.

### Step 4: Create Web Service

1. Click **"New +"** → **"Web Service"**
2. Connect your GitHub repo: `secre159/bsu-bokod-lms`
3. Configure:
   - **Name**: `bsu-bokod-lms`
   - **Runtime**: Docker
   - **Branch**: main
   - **Plan**: Free or Starter

### Step 5: Connect Web Service to MySQL

In **Environment** section, add:

| Key | Value |
|-----|-------|
| `DB_HOST` | `bsu-bokod-mysql` (your private service name) |
| `DB_PORT` | `3306` |
| `DB_NAME` | `libsystem4` |
| `DB_USER` | `libsystem_user` |
| `DB_PASSWORD` | (password you created for MySQL) |
| `MAIL_USERNAME` | your-email@gmail.com |
| `MAIL_PASSWORD` | your-gmail-app-password |
| `MAIL_FROM_ADDRESS` | your-email@gmail.com |

Click **"Create Web Service"**

### Step 6: Initialize Database

Once deployed, you need to create tables.

**Option A: Using Shell Access**
1. Go to your Web Service dashboard
2. Click **"Shell"** tab
3. Run:
```bash
mysql -h bsu-bokod-mysql -u libsystem_user -p libsystem4 < docker/init.sql
# Enter the MYSQL_PASSWORD when prompted
```

**Option B: Using MySQL Workbench (External)**
1. Go to MySQL Private Service → **Shell**
2. Run: `mysql -u root -p` (enter MYSQL_ROOT_PASSWORD)
3. Copy/paste contents of `docker/init.sql`
4. Or use port forwarding (advanced)

### Step 7: Test Your Site

Visit: `https://bsu-bokod-lms.onrender.com`

Login:
- Email: `admin@bsu.edu.ph`
- Password: `admin123`

✅ **Change password immediately!**

---

## 📋 Method 2: Using Blueprint (Faster)

I've created `render-mysql.yaml` for you, but Blueprint doesn't support external repos for private services.

**Workaround:**
1. Create MySQL Private Service manually (Steps 1-3 above)
2. Use Blueprint for web service only
3. Manually connect them via environment variables

---

## 💰 Cost Breakdown

- **MySQL Private Service**: Starter plan at $7/month (includes disk)
- **Web Service**: Free tier available (spins down) or Starter $7/month (always on)
- **Total**: $7-14/month depending on web service plan

---

## 🔧 Important Configuration

### Internal URL
Your web service connects to MySQL using:
- **Host**: `bsu-bokod-mysql` (the service name)
- **Port**: `3306`
- **No need for external connection strings!**

### Disk Mount Path
**CRITICAL**: Must be `/var/lib/mysql` exactly. This is where MySQL writes data.

### Security
- MySQL is only accessible within your Render workspace
- No external access (secure by default)
- Use strong passwords

---

## 🔍 Troubleshooting

### "Connection refused" error
- Check MySQL service is running
- Verify `DB_HOST` matches MySQL service name exactly
- Ensure both services are in same workspace

### "Access denied" error
- Verify `DB_USER` and `DB_PASSWORD` match MySQL env vars
- Check `MYSQL_DATABASE` name is correct

### Database not initialized
- Tables aren't created automatically
- Must run `docker/init.sql` via shell access
- Check logs for SQL errors

### Lost data after redeploy
- Make sure disk is configured correctly
- Mount path must be `/var/lib/mysql`
- Disk size should match your needs

---

## 📊 Connecting from External Tools

MySQL Private Service is not directly accessible from outside Render.

**To connect externally:**

1. **SSH Tunnel through Web Service:**
```bash
render ssh bsu-bokod-lms
mysql -h bsu-bokod-mysql -u libsystem_user -p
```

2. **Deploy Adminer (MySQL Admin UI):**
   - Create another web service with Adminer
   - Connect to `bsu-bokod-mysql:3306`
   - See: https://docs.render.com/deploy-adminer

3. **Use Shell Access:**
   - Most reliable method
   - Direct access via Render dashboard

---

## 🎯 Quick Checklist

- [ ] Create MySQL Private Service
- [ ] Add environment variables (MYSQL_*)
- [ ] Add disk with `/var/lib/mysql` mount path
- [ ] Wait for MySQL to start
- [ ] Create Web Service
- [ ] Add DB_* environment variables
- [ ] Add MAIL_* environment variables
- [ ] Deploy web service
- [ ] Initialize database via shell
- [ ] Test login
- [ ] Change admin password

---

## 💡 Pro Tips

1. **Use strong passwords** for MySQL
2. **Save credentials securely** - you'll need them
3. **Set disk size appropriately** - can't easily resize later
4. **Monitor disk usage** in Render dashboard
5. **Use mysqldump for backups** - don't rely only on snapshots
6. **Test connection** before initializing database

---

## 📦 Backup Strategy

**Recommended approach:**

```bash
# From web service shell
mysqldump -h bsu-bokod-mysql -u libsystem_user -p libsystem4 > backup.sql
```

- Run this regularly via cron job
- Store backups externally (S3, Google Drive, etc.)
- Test restore process periodically

---

## 🆘 Getting Help

- **Check logs**: Service → Logs tab
- **Check MySQL status**: Private Service → Metrics
- **Shell access**: Service → Shell tab
- **Render docs**: https://docs.render.com/deploy-mysql
- **Community**: https://community.render.com/

---

## 🎊 You're Ready!

Follow Method 1 above and you'll have MySQL running in 15 minutes.

**Cost**: $7-14/month
**Setup time**: 15 minutes
**Complexity**: Medium

Good luck! 🚀
