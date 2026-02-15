# 🚀 Render Deployment - PostgreSQL Setup

## 📌 Quick Summary

**Render doesn't offer MySQL on free tier**, so I've configured your app to use **PostgreSQL** instead (which IS free on Render forever!).

Don't worry - I've made it work with **BOTH**:
- 💻 **Local Development**: Still uses MySQL (no changes needed)
- ☁️ **Render Production**: Uses PostgreSQL (automatically)

---

## ✅ What I Did

1. ✅ Updated `render.yaml` → PostgreSQL database
2. ✅ Updated `Dockerfile` → Added PostgreSQL drivers
3. ✅ Created `docker/conn_postgres.php` → Smart connection (detects database type)
4. ✅ Created `docker/init_postgres.sql` → PostgreSQL schema
5. ✅ Updated `docker/entrypoint.sh` → Auto-configuration
6. ✅ Pushed everything to GitHub

**Your app now supports BOTH MySQL and PostgreSQL!** 🎉

---

## 🎯 Deploy Now (3 Steps)

### Step 1: Go to Render
Visit: https://dashboard.render.com/

### Step 2: Create Blueprint
1. Click **"New +"** → **"Blueprint"**
2. Connect GitHub repo: `secre159/bsu-bokod-lms`
3. Click **"Apply"**

Render will create:
- ✅ Web Service (your app)
- ✅ PostgreSQL Database (free forever!)

### Step 3: Add Email Settings
In Web Service → Environment, add:
```
MAIL_USERNAME = your-email@gmail.com
MAIL_PASSWORD = your-gmail-app-password
MAIL_FROM_ADDRESS = your-email@gmail.com
```

[Get Gmail App Password →](https://myaccount.google.com/apppasswords)

---

## 📊 Initialize Database

After deployment, you need to create the tables:

### Option A: Using pgAdmin (Recommended - GUI)
1. Download [pgAdmin](https://www.pgadmin.org/)
2. Get connection details from Render → Database → Connect
3. Connect to database
4. Run `docker/init_postgres.sql`

### Option B: Using psql (Command Line)
```bash
# Get connection URL from Render dashboard
psql postgresql://user:pass@host/dbname < docker/init_postgres.sql
```

### Option C: Using DBeaver (Multi-Database Tool)
1. Download [DBeaver](https://dbeaver.io/)
2. Create PostgreSQL connection
3. Run `docker/init_postgres.sql`

---

## 🎉 Test Your Site

1. Wait 5-10 minutes for deployment
2. Visit: `https://bsu-bokod-lms.onrender.com`
3. Login:
   - Email: `admin@bsu.edu.ph`
   - Password: `admin123`
4. **Change password immediately!**

---

## ❓ FAQ

### Q: Will my local development break?
**A: No!** Your local setup still uses MySQL. The app auto-detects:
- Local = MySQL
- Render = PostgreSQL

### Q: What about my existing MySQL data?
**A: Two options:**
1. Export from MySQL → Import to PostgreSQL (I can help with this)
2. Keep using MySQL locally, start fresh on Render

### Q: Is PostgreSQL hard to use?
**A: No difference!** PostgreSQL is actually MORE powerful than MySQL. Your PHP code works the same.

### Q: Can I use MySQL on Render?
**A: Not for free.** Options:
- Use PostgreSQL (free ✅)
- Use external MySQL service like Railway ($5/mo)
- Upgrade Render plan (expensive)

See `DATABASE_OPTIONS.md` for all options.

### Q: What if something goes wrong?
**A: Check logs:**
- Render Dashboard → Your Service → Logs
- Look for errors and connection messages
- Check `DATABASE_OPTIONS.md` troubleshooting section

---

## 💰 Cost Breakdown

### Free Tier (Forever):
- ✅ Web Service: Free (spins down after 15 min inactivity)
- ✅ PostgreSQL: Free (256MB RAM, 1GB storage)
- ✅ Total: **$0/month**

### Limitations:
- Cold start after inactivity (~30 seconds)
- Database goes to sleep
- Shared resources

### Upgrade to Pro ($7/mo):
- Always on (no cold starts)
- More database resources
- Custom domains
- Better support

---

## 📚 Documentation Files

- **`DATABASE_OPTIONS.md`** → Detailed comparison of all database options
- **`DEPLOY.md`** → Full deployment guide
- **`DEPLOYMENT_CHECKLIST.md`** → Quick checklist
- **`WARP.md`** → Development guide

---

## 🆘 Need Help?

1. **Database issues?** → Read `DATABASE_OPTIONS.md`
2. **Deployment issues?** → Read `DEPLOY.md`
3. **Can't connect?** → Check Render logs
4. **Need MySQL?** → See `DATABASE_OPTIONS.md` Option 2

---

## 🎊 Ready to Deploy!

Your code is ready. Just follow the 3 steps above and you'll be live in 10 minutes!

**Repository:** https://github.com/secre159/bsu-bokod-lms
**Render Dashboard:** https://dashboard.render.com/

Good luck! 🚀
