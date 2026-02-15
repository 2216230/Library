# System Settings & Backup Automation

## Overview

The System Settings page provides centralized configuration for library system operations, including general settings and backup automation.

## Files Created

### 1. `superadmin/system_settings.php` (270 lines)
Main settings management interface accessible only to SuperAdmin.

**Features:**
- **General Settings Section**
  - Library Name: Display name of the library system
  - Admin Email: Primary contact email for notifications
  - Session Timeout: Auto-logout duration (5-480 minutes)
  - Password Min Length: Password complexity requirement (6-32 chars)

- **Backup Automation Settings Section**
  - Enable/Disable automatic backups
  - Backup Frequency: Hourly, Daily, Weekly
  - Backup Time: Schedule time in 24-hour format
  - Backup Retention: Days to keep backups (7-365 days)
  - Cron job setup instructions

**Database Operations:**
- Creates `system_settings` table if not exists
- Uses INSERT ... ON DUPLICATE KEY UPDATE for upsert operations
- Logs all setting changes to activity_log

**Access Control:**
- SuperAdmin-only access (id = 1 check)
- All changes logged with activity_log

**UI Features:**
- Green and gold gradient headers matching theme
- Form validation for input ranges
- Informative descriptions for each setting
- Copy-paste cron job examples

---

### 2. `superadmin/backup_automation.php` (140 lines)
Automated backup handler for cron job integration.

**Functions:**

#### `auto` action (Called by cron)
```bash
0 2 * * * php /path/to/backup_automation.php auto
```
- Creates automatic backup with timestamp
- Uses mysqldump with single-transaction flag
- Stores in `/backups/` directory
- Logs backup creation to activity_log
- Automatically cleans old backups

#### `manual` action (Called from backup_manager.php)
```php
exec("php backup_automation.php manual")
```
- Creates manual backup on-demand
- Returns exit code (0 = success, 1 = failure)

#### `cleanup` action (Removes old backups)
```bash
0 3 * * * php /path/to/backup_automation.php cleanup
```
- Removes .sql files older than retention period
- Logs deletion to activity_log
- Safe retention check prevents accidental deletion

**Key Methods:**
- `getBackupSettings($conn)` - Retrieves all backup settings from system_settings table
- `cleanOldBackups($conn, $backup_dir, $retention_days)` - Removes expired backups
- `formatBytes($bytes)` - Converts bytes to human-readable format

**Environment Variables:**
- `DB_NAME` - Database name (default: libsystem5)
- `DB_HOST` - Database host (default: localhost)
- `DB_USER` - Database user (default: root)
- `DB_PASSWORD` - Database password (default: empty)

---

### 3. Updated: `includes/menubar.php`
Added System Settings link to SuperAdmin section.

**Changes:**
- New menu item: "System Settings" with fa-sliders icon
- Position: Between System Status and Backup Manager
- Link: `superadmin/system_settings.php`
- Styling: Red gradient (#DC143C) matching SuperAdmin section

---

## Database Schema

### `system_settings` table
```sql
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text,
  `setting_type` enum('string','boolean','number','json') DEFAULT 'string',
  `description` text,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)
```

### Default Settings
| setting_key | setting_value | setting_type |
|------------|---------------|--------------|
| library_name | Library System | string |
| admin_email | (empty) | string |
| session_timeout_minutes | 30 | number |
| password_min_length | 8 | number |
| auto_backup | 0 | boolean |
| backup_frequency | daily | string |
| backup_time | 02:00 | string |
| backup_retention_days | 30 | number |

---

## Cron Job Setup

### Required Cron Jobs

**1. Daily Backup at 2:00 AM**
```bash
0 2 * * * php /path/to/libsystem/admin/superadmin/backup_automation.php auto
```

**2. Weekly Backup Every Sunday at 2:00 AM**
```bash
0 2 * * 0 php /path/to/libsystem/admin/superadmin/backup_automation.php auto
```

**3. Clean Old Backups Daily at 3:00 AM**
```bash
0 3 * * * php /path/to/libsystem/admin/superadmin/backup_automation.php cleanup
```

### Notes
- Replace `/path/to/libsystem` with actual installation path
- Cron jobs require shell access to server
- Contact hosting provider for cron configuration
- Test with: `php backup_automation.php auto` (from SSH/Terminal)

---

## Usage Flow

### SuperAdmin Accessing Settings
1. Navigate to Menu → ⚙️ SUPERADMIN ONLY → System Settings
2. Update any General Settings or Backup Settings
3. Click "Save [Section Name] Settings" button
4. Receives success/error message
5. All changes logged to activity_log

### Setting Up Automated Backups
1. In System Settings page, enable "Enable Automatic Backups"
2. Select backup frequency (Hourly/Daily/Weekly)
3. Set backup time (24-hour format)
4. Set retention period (days to keep backups)
5. Click "Save Backup Settings"
6. Contact hosting provider to add cron jobs
7. Provide cron job examples from settings page

### Manual Backup Creation
1. Go to Menu → ⚙️ SUPERADMIN ONLY → Backup Manager
2. Click "Create Backup Now"
3. System calls `backup_automation.php manual`
4. Backup stored in `/backups/` directory
5. Action logged to activity_log

### Automatic Cleanup
- Old backups automatically deleted based on retention_days setting
- Cleanup happens during auto backup process
- Can be manually triggered via cron: `php backup_automation.php cleanup`

---

## Security Considerations

1. **Access Control**
   - System Settings page restricted to SuperAdmin only
   - Settings table modified only via system_settings.php
   - Activity log tracks all changes

2. **Backup Security**
   - Backups stored in `/backups/` directory
   - Should be outside webroot in production
   - Consider adding .htaccess to prevent web access

3. **Environment Variables**
   - Database credentials read from environment
   - No hardcoded passwords in scripts
   - Recommended: Use `.env` file with environment loader

4. **Retention Policy**
   - Prevents indefinite disk space usage
   - Old backups automatically deleted
   - Configurable per organization needs

---

## Error Handling

### Backup Failures
- Logged to activity_log with `AUTO_BACKUP_FAILED` action
- Empty or corrupt backups not retained
- Email notification possible (future enhancement)

### Missing Backup Directory
- Automatically created with 755 permissions
- Falls back safely if directory creation fails

### Invalid Settings
- Form input validated on client side
- Database constraints prevent invalid values
- Session success/error messages displayed

---

## Activity Logging Integration

All system setting changes logged to `activity_log` table:

| Action | Description | Triggered |
|--------|-------------|-----------|
| UPDATE_SETTINGS | Updated backup automation settings | Save button click |
| UPDATE_SETTINGS | Updated general system settings | Save button click |
| AUTO_BACKUP | Automatic backup created (size) | Cron job execution |
| AUTO_BACKUP_FAILED | Backup failed during execution | Cron job failure |
| BACKUP_DELETED | Old backup deleted: filename (size) | Auto cleanup |

---

## Future Enhancements

1. **Email Notifications**
   - Notify admin of backup completion
   - Alert on backup failures
   - Schedule status reports

2. **Backup Verification**
   - Test restore integrity automatically
   - Verify backup file completeness
   - Alert if backups are failing silently

3. **Differential Backups**
   - Only backup changed data
   - Reduce storage and bandwidth
   - Faster backup execution

4. **Cloud Backup Integration**
   - Upload backups to AWS S3
   - Google Drive backup sync
   - Dropbox integration

5. **Backup Scheduling UI**
   - Advanced scheduling interface
   - Multiple backup schedules
   - Different retention policies

---

## Testing Checklist

- [ ] System Settings page loads without errors
- [ ] General Settings save successfully
- [ ] Backup Settings save successfully
- [ ] Activity log records all changes
- [ ] Manual backup from Backup Manager works
- [ ] Cron job executes backup_automation.php
- [ ] Old backups deleted after retention period
- [ ] Backup files readable and contain valid SQL
- [ ] Test restore from backup file
- [ ] Error messages display properly
- [ ] SuperAdmin-only access enforced
- [ ] Settings persisted across page reloads

---

## Integration Points

- **activity_helper.php** - logActivity() function for audit trail
- **backup_manager.php** - Calls backup_automation.php for manual backups
- **menubar.php** - Links to system_settings.php
- **session.php** - User authentication check
- **conn.php** - Database connection

---

## File Structure

```
libsystem/admin/
├── superadmin/
│   ├── system_settings.php (NEW - Settings management)
│   ├── backup_automation.php (NEW - Backup handler)
│   ├── backup_manager.php (existing - Modified to call automation)
│   └── ...
├── includes/
│   ├── menubar.php (UPDATED - Added System Settings link)
│   └── ...
└── backups/ (Auto-created - Backup storage)
```

---

## Notes

- All timestamps stored in system_settings table with CURRENT_TIMESTAMP
- Backup files use ISO date format: `YYYY-MM-DD_HH-ii-ss`
- Retention period is configurable per organization policy
- Recommended retention: 30 days (balance storage vs. recovery window)
- Test backups before going live in production
