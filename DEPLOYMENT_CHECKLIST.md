# 🚀 Deployment Checklist - Moodle & Test Plugin

## Pre-Deployment Verification

### ✅ CI/CD Pipeline Status
- [ ] All tests passing in GitHub Actions
- [ ] No critical errors or warnings
- [ ] Code quality standards met
- [ ] Plugin validation successful

### ✅ Server Configuration
- [ ] GitHub Secrets configured:
  - `SERVER_HOST` - Your server hostname/IP
  - `SERVER_USERNAME` - SSH username
  - `SERVER_SSH_KEY` - SSH private key
  - `SERVER_PORT` - SSH port (default: 22)
  - `SERVER_BASE_PATH` - Base path on server (e.g., /var/www)
  - `MOODLE_PATH` - Path to Moodle installation (for plugin updates)
  - `MOODLE_URL` - Your Moodle site URL
  - `FIRST_TIME_DEPLOYMENT` - Set to "true" for first deployment, "false" for updates

### ✅ Server Requirements
- [ ] PHP version compatible (8.1, 8.2, or 8.3)
- [ ] MySQL/MariaDB database ready
- [ ] SSH access configured
- [ ] Git repository accessible on server
- [ ] Proper file permissions
- [ ] Web server (Apache/Nginx) configured

## Deployment Process

### 🔄 Automatic Deployment Flow
1. **Push to main branch** → Triggers CI/CD testing
2. **All tests pass** → Automatically triggers deployment
3. **Deployment type determined** → Based on `FIRST_TIME_DEPLOYMENT` secret
4. **Deployment workflow** → Deploys complete Moodle or plugin updates
5. **Verification** → Confirms successful deployment

### 📋 First-Time Deployment (Complete Moodle)
**Set `FIRST_TIME_DEPLOYMENT=true` in GitHub Secrets**

1. **Complete Moodle Installation:**
   - Clones entire repository to server
   - Sets up proper file permissions
   - Creates moodledata directory
   - Copies config-dist.php to config.php

2. **Post-Deployment Setup:**
   ```bash
   # Configure database settings in config.php
   nano /path/to/moodle/config.php
   
   # Run Moodle installation
   php admin/cli/install.php
   
   # Set FIRST_TIME_DEPLOYMENT=false for future updates
   ```

### 📋 Plugin Update Deployment
**Set `FIRST_TIME_DEPLOYMENT=false` in GitHub Secrets**

1. **Plugin-Specific Updates:**
   - Pulls latest changes from git
   - Updates only the plugin files
   - Sets proper permissions for plugin
   - Clears cache and runs upgrades

## Post-Deployment Verification

### ✅ First-Time Deployment Check
- [ ] Complete Moodle installation exists
- [ ] Plugin directory exists in local/
- [ ] moodledata directory created
- [ ] config.php file exists
- [ ] Database connection working
- [ ] Moodle installation wizard accessible

### ✅ Plugin Update Check
- [ ] Plugin appears in "Site administration" > "Plugins" > "Local plugins"
- [ ] Plugin status shows as "Active"
- [ ] No error messages in Moodle notifications
- [ ] Plugin functionality working correctly

### ✅ Functionality Testing
- [ ] Navigate to plugin page: "Site administration" > "Plugins" > "Local plugins" > "Test Plugin"
- [ ] Plugin page loads correctly
- [ ] All features work as expected
- [ ] User permissions function properly

### ✅ Database Verification
- [ ] Plugin tables created successfully
- [ ] No database errors in logs
- [ ] Plugin version recorded correctly

### ✅ Performance Check
- [ ] Page load times acceptable
- [ ] No performance degradation
- [ ] Cache working properly

## Rollback Plan

### 🔄 First-Time Deployment Rollback
```bash
# Stop web server
sudo systemctl stop apache2  # or nginx

# Restore from backup
cp -r /path/to/server/moodle.backup.* /path/to/server/moodle

# Restart web server
sudo systemctl start apache2  # or nginx
```

### 🔄 Plugin Update Rollback
```bash
# Stop web server
sudo systemctl stop apache2  # or nginx

# Restore plugin from backup
cp -r /path/to/moodle/local/test_plugin.backup.* /path/to/moodle/local/test_plugin

# Restart web server
sudo systemctl start apache2  # or nginx

# Clear cache
php admin/cli/purge_caches.php
```

## Monitoring

### 📊 Post-Deployment Monitoring
- [ ] Monitor error logs for 24 hours
- [ ] Check plugin performance
- [ ] Verify user feedback
- [ ] Monitor server resources

### 🔔 Alerts to Watch For
- [ ] PHP errors in logs
- [ ] Database connection issues
- [ ] Plugin functionality errors
- [ ] Performance degradation

## Success Criteria

### 🎯 First-Time Deployment Success
- [ ] Complete Moodle installation deployed
- [ ] Plugin included in installation
- [ ] All files and directories created
- [ ] Proper permissions set
- [ ] Ready for Moodle configuration

### 🎯 Plugin Update Success
- [ ] Plugin updated successfully
- [ ] All functionality working
- [ ] No critical errors
- [ ] Performance maintained

---

**Deployment Date:** [Insert Date]  
**Deployment Type:** [First-time / Plugin Update]  
**Deployed Version:** [Insert Version]  
**Deployed By:** [Insert Name]  
**Server Environment:** [Insert Details]

## 🎉 Ready for Deployment!

Your deployment system is ready to handle both first-time Moodle installations and plugin updates!

**Next Steps:**
1. ✅ Configure GitHub Secrets
2. 🚀 Set `FIRST_TIME_DEPLOYMENT=true` for first deployment
3. 📊 Push changes to main branch
4. 🎯 Monitor the CI/CD pipeline
5. ⚙️ Complete Moodle configuration (first-time only)
6. 🔄 Set `FIRST_TIME_DEPLOYMENT=false` for future updates

**Good luck with your deployment!** 🚀 