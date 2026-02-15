# SuperAdmin Tools

This folder contains exclusive SuperAdmin-only tools that should not be accessible to regular admin users.

## Access Control

All files in this folder have built-in access control that checks:
- User is logged in as an admin
- User ID equals 1 (the first/main admin account)

If a regular admin (id != 1) tries to access these files, they will be redirected with an error message.

## Files

### backup_manager.php
Manages database backups including:
- Creating new backups
- Downloading existing backups
- Restoring from backups
- Deleting old backups

**Accessible via:** `/admin/superadmin/backup_manager.php` or menu link

### database_schema_fix.php
Applies database schema updates:
- Creates missing tables
- Adds missing columns
- Creates performance indexes
- Fixes foreign key constraints

**Accessible via:** `/admin/superadmin/database_schema_fix.php` or menu link

## Menu Integration

Links to these tools are in the menubar under the red "⚙️ SUPERADMIN ONLY" section.

## Security Notes

1. Access is restricted to the first admin account (id = 1)
2. All operations require admin session
3. Critical operations (restore, apply fixes) require user confirmation
4. Database operations use proper escaping and prepared statements
