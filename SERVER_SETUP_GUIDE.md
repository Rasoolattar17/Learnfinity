# 🖥️ Server Setup Guide for Moodle Deployment

## Your Server Configuration

Based on your requirements, your server should be set up as follows:

### **Directory Structure:**
```
/var/www/html/
├── moodle/          # Moodle installation (will be created by deployment)
└── moodledata/      # Moodle data directory (will be created by deployment)
```

### **Required GitHub Secrets:**

| Secret Name | Value | Description |
|-------------|-------|-------------|
| `SERVER_HOST` | `your-server-ip` | Your server's IP address |
| `SERVER_USERNAME` | `ubuntu` (or your username) | SSH username |
| `SERVER_SSH_KEY` | `-----BEGIN OPENSSH PRIVATE KEY-----...` | Your private SSH key |
| `SERVER_BASE_PATH` | `/var/www/html` | Base directory where Moodle will be installed |
| `FIRST_TIME_DEPLOYMENT` | `true` | Set to true for first deployment |

## Server Preparation Steps

### **1. Create Base Directory (if not exists):**
```bash
sudo mkdir -p /var/www/html
sudo chown www-data:www-data /var/www/html
sudo chmod 755 /var/www/html
```

### **2. Verify Directory Permissions:**
```bash
ls -la /var/www/html
```

### **3. Test SSH Access:**
```bash
ssh username@your-server-ip
cd /var/www/html
pwd
```

## What the Deployment Will Do

### **First-Time Deployment Process:**
1. **Navigate to:** `/var/www/html`
2. **Create backup** of existing moodle (if exists)
3. **Clone repository** to `/var/www/html/moodle`
4. **Set permissions** for entire Moodle installation
5. **Create moodledata** at `/var/www/html/moodledata`
6. **Copy config.php** from config-dist.php

### **Final Structure After Deployment:**
```
/var/www/html/
├── moodle/
│   ├── admin/
│   ├── blocks/
│   ├── local/
│   │   └── test_plugin/    # Your plugin
│   ├── mod/
│   ├── theme/
│   ├── config.php
│   └── version.php
└── moodledata/
    ├── cache/
    ├── sessions/
    └── ...
```

## Post-Deployment Steps

### **1. Configure Database:**
Edit `/var/www/html/moodle/config.php`:
```php
<?php
$CFG = new stdClass();
$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'moodle_user';
$CFG->dbpass    = 'your_password';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array(
    'dbpersist' => 0,
    'dbport' => '',
    'dbsocket' => '',
    'dbcollation' => 'utf8mb4_unicode_ci',
);

$CFG->wwwroot   = 'http://your-domain.com/moodle';
$CFG->dataroot  = '/var/www/html/moodledata';
$CFG->admin     = 'admin';
$CFG->directorypermissions = 02777;
require_once(__DIR__ . '/lib/setup.php');
```

### **2. Set Web Server Permissions:**
```bash
sudo chown -R www-data:www-data /var/www/html/moodle
sudo chown -R www-data:www-data /var/www/html/moodledata
sudo chmod -R 755 /var/www/html/moodle
sudo chmod -R 777 /var/www/html/moodledata
```

### **3. Configure Web Server:**
For Apache, create `/etc/apache2/sites-available/moodle`:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/moodle
    
    <Directory /var/www/html/moodle>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/moodle_error.log
    CustomLog ${APACHE_LOG_DIR}/moodle_access.log combined
</VirtualHost>
```

### **4. Enable Site:**
```bash
sudo a2ensite moodle
sudo systemctl reload apache2
```

## Testing the Deployment

### **1. Run the Test Workflow:**
Go to GitHub Actions → "Simple Deploy Test" → Run workflow

### **2. Check Deployment:**
After deployment, verify:
```bash
ls -la /var/www/html/
ls -la /var/www/html/moodle/
ls -la /var/www/html/moodle/local/test_plugin/
```

### **3. Access Moodle:**
Visit: `http://your-server-ip/moodle`

## Troubleshooting

### **Common Issues:**

**❌ Permission Denied:**
```bash
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

**❌ Directory Not Found:**
```bash
sudo mkdir -p /var/www/html
sudo chown www-data:www-data /var/www/html
```

**❌ SSH Connection Failed:**
- Verify SSH key is added to server
- Check username and host are correct
- Ensure SSH service is running

---

**Ready to deploy?** Configure the GitHub secrets and run the deployment workflow! 🚀 