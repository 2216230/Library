# Render Disk Configuration for Database Restore

## Problem
- WAF blocks large file uploads through web interface
- PHP upload limits restrict file sizes

## Solution: Use Render Disk for temporary upload storage

### Setup Steps:

1. **Create a Render Disk** (in Render Dashboard):
   - Go to your service → Settings → Disks
   - Click "Add Disk"
   - Name: `backup-uploads`
   - Mount Path: `/var/uploads`
   - Size: 1 GB (minimum)
   - Click "Create"

2. **Update Dockerfile** to create upload directory:
   ```dockerfile
   RUN mkdir -p /var/uploads && chown www-data:www-data /var/uploads
   ```

3. **Update backup restore page** to use disk mount:
   - Change upload target from `tmp_name` to `/var/uploads/`
   - Process files from persistent disk instead of temp
   - Cleanup after successful restore

### Benefits:
- ✓ Bypasses WAF (files written directly to disk)
- ✓ No PHP upload limit issues
- ✓ Can handle multi-GB backup files
- ✓ Persistent storage for failed uploads (can retry)

### Alternative: Pre-signed Upload URLs
Instead of direct upload through PHP, use:
1. Generate pre-signed upload URL
2. Upload via JavaScript directly to disk
3. Trigger restore after upload completes

This completely bypasses PHP upload limits and WAF restrictions.
