# Moodle Plugin Deployment Guide

This guide provides step-by-step instructions for deploying your `local/test_plugin` to a production server.

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Deployment Methods](#deployment-methods)
3. [Manual Deployment](#manual-deployment)
4. [Automated Deployment](#automated-deployment)
5. [Post-Deployment Verification](#post-deployment-verification)
6. [Rollback Procedures](#rollback-procedures)

## Pre-Deployment Checklist

### ✅ Code Quality Verification
- [ ] All CI/CD tests passing
- [ ] Code quality standards met (phpcs, phpmd, phpcpd)
- [ ] Plugin validation successful
- [ ] No critical errors or warnings

### ✅ Server Requirements
- [ ] PHP version compatible (8.1, 8.2, or 8.3)
- [ ] Moodle version compatible (4.3, 4.4, or 4.5)
- [ ] Required PHP extensions installed
- [ ] Database permissions configured
- [ ] File system permissions set

### ✅ Backup Procedures
- [ ] Database backup completed
- [ ] Moodle installation backup completed
- [ ] Current plugin backup (if updating)

## Deployment Methods

### Method 1: Manual Deployment (Recommended for first deployment)
Simple file upload via FTP/SFTP or file manager.

### Method 2: Git-based Deployment
Using Git hooks or deployment scripts.

### Method 3: Automated CI/CD Deployment
Using GitHub Actions for automated deployment.

## Manual Deployment

### Step 1: Prepare Plugin Files
```bash
# Create deployment package
cd /path/to/your/moodle
zip -r test_plugin_deployment.zip local/test_plugin/ -x "*.git*" "*.DS_Store*"
```

### Step 2: Upload to Server
1. **Via FTP/SFTP:**
   ```bash
   # Upload the zip file to your server
   scp test_plugin_deployment.zip user@your-server:/tmp/
   
   # SSH into your server
   ssh user@your-server
   
   # Extract to Moodle local directory
   cd /path/to/moodle
   unzip /tmp/test_plugin_deployment.zip
   ```

2. **Via File Manager:**
   - Upload `test_plugin_deployment.zip` to your server
   - Extract to `/path/to/moodle/local/` directory

### Step 3: Set Permissions
```bash
# Set correct file permissions
chmod -R 755 local/test_plugin/
chown -R www-data:www-data local/test_plugin/
```

### Step 4: Install Plugin
1. **Access Moodle Admin Panel:**
   - Go to `https://your-moodle-site.com/admin/`
   - Navigate to "Site administration" > "Notifications"

2. **Plugin Installation:**
   - Moodle should automatically detect the new plugin
   - Follow the installation prompts
   - Complete any database upgrades if required

## Automated Deployment

### GitHub Actions Deployment (Optional)

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    tags:
      - 'v*'

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
        
      - name: Create deployment package
        run: |
          cd local/test_plugin
          zip -r ../test_plugin.zip . -x "*.git*" "*.DS_Store*"
          
      - name: Deploy to server
        uses: appleboy/scp-action@v0.1.7
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.KEY }}
          source: "local/test_plugin.zip"
          target: "/tmp/"
          
      - name: Install on server
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.KEY }}
          script: |
            cd /path/to/moodle
            unzip -o /tmp/test_plugin.zip -d local/
            chmod -R 755 local/test_plugin/
            chown -R www-data:www-data local/test_plugin/
            php admin/cli/upgrade.php --non-interactive
```

## Post-Deployment Verification

### Step 1: Plugin Installation Check
1. **Admin Panel Verification:**
   - Go to "Site administration" > "Plugins" > "Local plugins"
   - Verify "Test Plugin" is listed and active

2. **Database Verification:**
   ```sql
   -- Check if plugin tables exist
   SHOW TABLES LIKE 'local_test_plugin%';
   
   -- Check plugin version
   SELECT * FROM mdl_config_plugins WHERE plugin = 'local_test_plugin';
   ```

### Step 2: Functionality Testing
1. **Access Test:**
   - Navigate to "Site administration" > "Plugins" > "Local plugins" > "Test Plugin"
   - Verify the plugin page loads correctly

2. **Feature Testing:**
   - Test all plugin functionality
   - Verify user permissions work correctly
   - Check error handling

### Step 3: Performance Check
1. **Page Load Times:**
   - Monitor plugin page load performance
   - Check for any database query issues

2. **Error Logs:**
   - Review Moodle error logs
   - Check for any plugin-related errors

## Rollback Procedures

### Quick Rollback
```bash
# Stop web server
sudo systemctl stop apache2  # or nginx

# Restore from backup
cp -r /backup/local/test_plugin/ /path/to/moodle/local/

# Restart web server
sudo systemctl start apache2  # or nginx
```

### Database Rollback
```bash
# Restore database from backup
mysql -u username -p database_name < backup.sql

# Run Moodle upgrade
php admin/cli/upgrade.php --non-interactive
```

## Security Considerations

### File Permissions
```bash
# Secure file permissions
find local/test_plugin/ -type f -exec chmod 644 {} \;
find local/test_plugin/ -type d -exec chmod 755 {} \;
chmod 644 local/test_plugin/version.php
```

### Database Security
- Ensure database user has minimal required permissions
- Use prepared statements for all database queries
- Validate all user inputs

### Access Control
- Verify plugin capabilities are properly configured
- Test user role permissions
- Ensure admin-only features are protected

## Monitoring and Maintenance

### Regular Checks
- Monitor plugin performance
- Review error logs
- Check for Moodle updates compatibility
- Update plugin as needed

### Backup Strategy
- Regular database backups
- Plugin file backups before updates
- Version control for all changes

---

**Deployment Date:** [Insert Date]  
**Deployed Version:** [Insert Version]  
**Deployed By:** [Insert Name]  
**Server Environment:** [Insert Details] 