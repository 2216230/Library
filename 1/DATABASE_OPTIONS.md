# Database Options for Render Deployment

## 🎯 Quick Answer

**Render does NOT offer MySQL on the free tier.** Here are your options:

---

## ✅ Option 1: Use PostgreSQL (Recommended - FREE)

PostgreSQL is **free forever** on Render with some limitations. I've already configured your app to support it!

### What Changed:
- ✅ Updated `render.yaml` to use PostgreSQL
- ✅ Updated `Dockerfile` to install PostgreSQL drivers
- ✅ Created `docker/conn_postgres.php` for PostgreSQL connection
- ✅ Created `docker/init_postgres.sql` for PostgreSQL schema
- ✅ Updated `entrypoint.sh` to auto-detect database type

### Deploy Steps:

1. **Push the updated code:**
   ```powershell
   git add .
   git commit -m "Add PostgreSQL support for Render"
   git push origin main
   ```

2. **Deploy on Render:**
   - Go to https://dashboard.render.com/
   - Click "New +" → "Blueprint"
   - Select your repository
   - Render will create PostgreSQL database automatically

3. **Initialize the Database:**
   ```bash
   # Connect via Render dashboard or psql command
   psql -h <hostname> -U <username> -d <database> < docker/init_postgres.sql
   ```

   Or use a PostgreSQL GUI client like:
   - [pgAdmin](https://www.pgadmin.org/)
   - [DBeaver](https://dbeaver.io/)
   - [TablePlus](https://tableplus.com/)

### PostgreSQL Free Tier:
- ✅ **Free forever** (not a trial)
- ✅ 256 MB RAM
- ✅ 1 GB storage
- ✅ No credit card required
- ⚠️ Database sleeps after inactivity (15 min)
- ⚠️ Shared resources

**Perfect for development and small projects!**

---

## 💰 Option 2: Use External MySQL (Paid)

If you absolutely need MySQL, use an external provider:

### A. Railway ($5/month)
- Offers MySQL/PostgreSQL
- Easy setup
- Better than Render for MySQL

**Setup:**
1. Sign up at https://railway.app/
2. Create MySQL database
3. Copy connection details
4. Add to Render as environment variables:
   ```
   DB_HOST=railway-host
   DB_USER=railway-user
   DB_PASSWORD=railway-password
   DB_NAME=libsystem4
   DB_PORT=3306
   ```

### B. PlanetScale (Generous Free Tier)
- MySQL-compatible serverless database
- 5 GB storage free
- Best MySQL option for free tier

**Setup:**
1. Sign up at https://planetscale.com/
2. Create database
3. Get connection string
4. Add to Render environment variables

### C. Amazon RDS (Paid)
- AWS managed MySQL
- 12-month free tier (750 hours/month)
- Professional grade

### D. Aiven (Free Trial)
- Multiple database types
- Free trial for MySQL
- $10-20/month after trial

---

## 🔄 Option 3: Keep MySQL Locally, PostgreSQL on Render

Your app now supports BOTH! The connection automatically detects:
- **Local development**: Uses MySQL (localhost)
- **Render deployment**: Uses PostgreSQL (DATABASE_URL)

This is great for:
- ✅ No changes to local setup
- ✅ Free Render deployment
- ✅ Easy development workflow

---

## 🆚 PostgreSQL vs MySQL - What's Different?

For your PHP application, **very little!** Here's what you should know:

### Syntax Differences (handled automatically):
| Feature | MySQL | PostgreSQL |
|---------|-------|------------|
| Auto-increment | `AUTO_INCREMENT` | `SERIAL` ✅ Fixed |
| Quotes | Backticks \` | Double quotes " ✅ Works |
| Enum types | `ENUM('a','b')` | `VARCHAR CHECK` ✅ Fixed |
| Date functions | `NOW()` | `CURRENT_TIMESTAMP` ✅ Fixed |

### Your app uses:
- ✅ **PDO** (works with both)
- ✅ Standard SQL (compatible)
- ✅ Simple queries (no MySQL-specific features)

**Migration is painless!** 🎉

---

## 📝 How to Initialize Database on Render

### Using Render Dashboard (Easiest):

1. Go to your database in Render dashboard
2. Click **"Connect"** tab
3. Use the **"External Connection"** details
4. Connect with a GUI client (pgAdmin, DBeaver, etc.)
5. Run `docker/init_postgres.sql`

### Using psql Command Line:

```bash
# Get connection details from Render dashboard
psql postgresql://username:password@hostname/database < docker/init_postgres.sql
```

### Using Web Interface:

Some free PostgreSQL GUI tools:
- **pgAdmin** (Desktop app)
- **DBeaver** (Universal database tool)
- **Beekeeper Studio** (Modern, simple)
- **TablePlus** (Mac/Windows, free tier)

---

## 🎯 Recommended Choice

**Use PostgreSQL (Option 1)** because:
- ✅ Completely free on Render
- ✅ Your code already supports it
- ✅ No need for external services
- ✅ One less thing to manage
- ✅ PostgreSQL is more powerful than MySQL anyway

The only reason to use MySQL is if you have existing data that's hard to migrate.

---

## 🔧 Testing Your Setup

After deployment, check the logs:

```
# Good signs in logs:
✅ "Detected PostgreSQL DATABASE_URL from Render"
✅ "PostgreSQL configuration applied successfully"
✅ "Apache started successfully"

# Bad signs:
❌ "Connection failed"
❌ "Database not found"
```

---

## 🆘 Troubleshooting

### "Connection failed" error:
1. Check database is running in Render dashboard
2. Verify DATABASE_URL is set automatically
3. Check logs for specific error messages

### "Table doesn't exist" error:
- You forgot to run `init_postgres.sql`
- Connect to database and run the schema

### Local development broke:
- Don't worry! It still uses MySQL locally
- Only production uses PostgreSQL

---

## 💡 Pro Tips

1. **Keep your local MySQL setup** - no need to change it
2. **PostgreSQL is actually better** - more features, better performance
3. **Render auto-sets DATABASE_URL** - you don't need to configure it
4. **Use migrations in the future** - for easier database updates
5. **Backup regularly** - Render provides backup options

---

## 📊 Quick Comparison Table

| Option | Cost | Setup Time | Maintenance | Best For |
|--------|------|------------|-------------|----------|
| PostgreSQL (Render) | **FREE** | 5 min | None | ✅ Most users |
| Railway MySQL | $5/mo | 10 min | Low | Need MySQL |
| PlanetScale | Free trial | 15 min | Medium | Scalability |
| Local MySQL only | Free | 0 min | High | Development only |

---

## 🚀 Next Steps

1. Commit and push the updated code
2. Deploy to Render with PostgreSQL
3. Initialize database with `init_postgres.sql`
4. Login and test your app

**Need help?** Check DEPLOY.md for step-by-step instructions.
