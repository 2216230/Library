# Database Restore Instructions

## Problem
If you get "File exceeds upload_max_filesize" error when trying to restore via the web interface, use the CLI method below.

## CLI Restore Method (Recommended for Large Files)

This method bypasses PHP upload limits entirely by running the restore directly on the Render server.

### Step 1: Download Your Backup from Admin Panel
1. Go to Admin Panel → SYSTEM → Backup & Restore
2. Click "Download Backup" to get your `.sql` file
3. Save it somewhere accessible (e.g., Desktop, Dropbox, Google Drive)

### Step 2: Upload Backup File to a Temporary URL
Upload your backup file to a temporary file-sharing service that provides a direct download link:
- **Dropbox**: Upload file, share link, change `dl=0` to `dl=1` in URL
- **Google Drive**: Use a public link
- **File.io**: https://www.file.io (temporary, single-use)
- **WeTransfer**: https://wetransfer.com

### Step 3: Access Render Shell
1. Go to your Render dashboard: https://dashboard.render.com
2. Click on your web service "bsu-bokod-lms"
3. Click the "Shell" tab in the top menu
4. Wait for shell to connect

### Step 4: Download and Restore
In the Render shell, run these commands:

```bash
# Download your backup file (replace URL with your actual file URL)
curl -L -o backup.sql "YOUR_BACKUP_FILE_URL_HERE"

# Verify the file was downloaded
ls -lh backup.sql

# Run the restore script
php restore_backup.php backup.sql
```

When prompted, type `YES` to confirm the restore.

### Step 5: Clean Up
After successful restore, delete the backup file:
```bash
rm backup.sql
```

## Example Complete Workflow

```bash
# 1. Download backup from Dropbox
curl -L -o backup.sql "https://www.dropbox.com/s/abc123/backup_libsystem4_2025-11-20.sql?dl=1"

# 2. Check file size
ls -lh backup.sql

# 3. Restore database
php restore_backup.php backup.sql
# (Type YES when prompted)

# 4. Clean up
rm backup.sql
```

## Alternative: Using MySQL Client Directly

If you have the MySQL command-line client available:

```bash
# Download backup
curl -L -o backup.sql "YOUR_BACKUP_URL"

# Restore using mysql client
mysql -h $DB_HOST -u $DB_USER -p$DB_PASSWORD $DB_NAME < backup.sql

# Clean up
rm backup.sql
```

## Troubleshooting

### "curl: command not found"
Try using `wget` instead:
```bash
wget -O backup.sql "YOUR_BACKUP_URL"
```

### "File not found" error
Check if the file downloaded correctly:
```bash
ls -lh backup.sql
head backup.sql
```

### Permission issues
Make sure the restore script is executable:
```bash
chmod +x restore_backup.php
```

### Need to restore from local file
If you can't upload to a URL, you can paste the SQL directly (only for small backups):
```bash
cat > backup.sql << 'EOF'
-- Paste your SQL content here
-- ...
EOF

php restore_backup.php backup.sql
```

## Web Interface Method (For Small Files Only)

If your backup file is small enough (under the PHP upload limit shown on the page):
1. Go to Admin Panel → SYSTEM → Backup & Restore
2. Check the "Upload Limits" section to see max file size
3. Click "Choose File" and select your `.sql` backup
4. Click "Restore Database"
5. Confirm the action

## Need Help?

If you encounter any issues:
1. Check the Render logs for error messages
2. Verify database credentials are correct
3. Ensure the backup file is valid SQL format
4. Contact support with the specific error message
