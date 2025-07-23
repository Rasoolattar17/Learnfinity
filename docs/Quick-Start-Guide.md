# 🚀 Learnfinity CI/CD Quick Start Guide

Get your **Moodle Plugin CI/CD Pipeline** running in under **15 minutes**!

## 📋 Prerequisites Checklist

Before starting, ensure you have:

- [ ] **Server** with Ubuntu 18.04+ (or similar Linux distribution)
- [ ] **SSH access** to your server  
- [ ] **Web server** (Nginx/Apache) installed
- [ ] **PHP 8.1+** with extensions installed
- [ ] **MySQL 8.0+** or PostgreSQL database
- [ ] **Git** installed on server
- [ ] **GitHub repository** ready

## ⚡ Step 1: Server Setup (5 minutes)

### **SSH to Your Server**
```bash
ssh your_username@your_server_ip
```

### **Install Required PHP Extensions**
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php8.3-{mbstring,xml,intl,gd,curl,zip,mysqli,cli,fpm}

# Or for different PHP version
sudo apt install php8.1-{mbstring,xml,intl,gd,curl,zip,mysqli,cli,fpm}
```

### **Fix Directory Permissions**
```bash
# Replace 'your_username' with your actual SSH username
sudo chown -R your_username:www-data /var/www/html
sudo chmod -R 775 /var/www/html

# Test write permissions
touch /var/www/html/test && rm /var/www/html/test && echo "✅ Permissions OK"
```

## 🔑 Step 2: GitHub Secrets Configuration (3 minutes)

### **Navigate to GitHub Secrets**
1. Go to your repository on GitHub
2. Click **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**

### **Add Required Secrets**

#### **For First-Time Moodle Installation:**
```bash
SECRET NAME: FIRST_TIME_DEPLOYMENT
SECRET VALUE: true

SECRET NAME: SERVER_HOST  
SECRET VALUE: your.server.ip.address

SECRET NAME: SERVER_USERNAME
SECRET VALUE: your_ssh_username

SECRET NAME: SERVER_BASE_PATH
SECRET VALUE: /var/www/html

SECRET NAME: SERVER_SSH_KEY
SECRET VALUE: [Your private SSH key - see below]
```

### **SSH Key Setup**
```bash
# On your local machine, generate SSH key
ssh-keygen -t rsa -b 4096 -f ~/.ssh/github_deploy_key

# Copy public key to server
ssh-copy-id -i ~/.ssh/github_deploy_key.pub your_username@your_server_ip

# Copy private key content to GitHub
cat ~/.ssh/github_deploy_key
# Copy the ENTIRE output (including -----BEGIN/END lines) to SERVER_SSH_KEY secret
```

## 📁 Step 3: Repository Structure (2 minutes)

### **Ensure Your Repository Has These Files:**
```
your-repo/
├── .github/workflows/
│   ├── learnfinity-ci.yml     # ✅ Already created
│   └── deploy.yml             # ✅ Already created
├── local/hello/               # ✅ Test plugin already created
│   ├── version.php
│   ├── lang/en/local_hello.php
│   └── ... (other plugin files)
├── admin/                     # ✅ Moodle core files
├── mod/                       # ✅ Moodle core files
├── version.php                # ✅ Moodle version file
└── config-dist.php            # ✅ Moodle config template
```

## 🧪 Step 4: Test Your Setup (5 minutes)

### **Trigger Test Workflow**
1. **Make a small change** to trigger CI:
   ```bash
   # Edit README or any file
   echo "Testing CI/CD Pipeline - $(date)" >> README.md
   
   # Commit and push
   git add .
   git commit -m "Test CI/CD pipeline"
   git push origin master
   ```

2. **Monitor Progress**:
   - Go to **GitHub** → **Actions** tab
   - Watch **"Learnfinity Plugin CI"** workflow run
   - After CI passes, **"Deploy Moodle"** should start automatically

### **Expected Results**
```
✅ Learnfinity Plugin CI
   ├── ✅ Auto-detected plugin: local/hello  
   ├── ✅ PHP Lint passed
   ├── ✅ Code standards passed
   ├── ✅ PHPUnit tests passed
   └── ✅ All tests completed successfully

✅ Deploy Moodle (First Time)
   ├── ✅ SSH connection successful
   ├── ✅ Moodle cloned to server
   ├── ✅ Permissions set correctly  
   ├── ✅ config.php created
   └── ✅ Deployment completed successfully
```

## 🎯 Step 5: Verify Installation

### **Check Your Server**
```bash
# SSH to server and verify
ssh your_username@your_server_ip

# Check Moodle installation
ls -la /var/www/html/moodle/
# Should show: version.php, config.php, admin/, local/, etc.

# Check your plugin
ls -la /var/www/html/moodle/local/hello/
# Should show: version.php, lang/, etc.

# Check moodledata directory
ls -la /var/www/html/moodledata/
# Should exist and be writable
```

### **Access Moodle in Browser**
1. Open browser and go to: `http://your_server_ip/moodle`
2. Complete Moodle installation wizard
3. Use database credentials for your server

## 🔄 Using the System Daily

### **Normal Development Workflow**
```bash
# 1. Develop your plugin
mkdir local/mynewplugin
# ... create plugin files

# 2. Commit and push
git add .
git commit -m "Add new plugin feature"
git push origin master

# 3. Automatic process happens:
# → CI tests run automatically
# → If tests pass, deployment runs automatically
# → Plugin appears on server
```

### **Manual Triggers (if needed)**
- **Test Only**: Go to Actions → "Learnfinity Plugin CI" → "Run workflow"
- **Deploy Only**: Go to Actions → "Deploy Moodle" → "Run workflow"

## 🔧 Switch to Plugin Update Mode

After your first successful deployment:

### **Update GitHub Secrets**
```bash
# Change these secrets:
FIRST_TIME_DEPLOYMENT: false
MOODLE_PATH: /var/www/html/moodle

# Remove these secrets (no longer needed):
SERVER_BASE_PATH: [delete this secret]
```

Now future deployments will only update plugins, not reinstall Moodle.

## ❗ Common Quick Fixes

### **Problem: SSH Connection Failed**
```bash
# Test SSH connection manually
ssh your_username@your_server_ip

# If fails, check:
- SERVER_HOST secret has correct IP
- SERVER_USERNAME secret has correct username  
- SERVER_SSH_KEY secret has complete private key (including BEGIN/END lines)
```

### **Problem: Permission Denied**
```bash
# Fix permissions on server
sudo chown -R your_username:www-data /var/www/html
sudo chmod -R 775 /var/www/html
```

### **Problem: Plugin Not Detected**
```bash
# Ensure plugin structure:
local/pluginname/
├── version.php              # Must exist
├── lang/en/local_pluginname.php  # Must exist
└── lib.php                  # Recommended
```

### **Problem: CI Tests Failing**
```bash
# Check common issues:
- Missing semicolons in PHP
- Incorrect PHPDoc format
- Missing language strings
- Test failures

# View detailed logs in GitHub Actions tab
```

## 🎉 Success Indicators

You'll know everything is working when you see:

- [ ] ✅ **GitHub Actions** show green checkmarks
- [ ] 📧 **No failure emails** from GitHub  
- [ ] 🖥️ **Moodle accessible** via browser
- [ ] 🧩 **Plugin visible** in Moodle admin
- [ ] 🔄 **Automatic deployments** working on push

## 📞 Need Help?

- **View Detailed Logs**: GitHub repository → Actions tab → Click failed workflow
- **Check Documentation**: `docs/CI-CD-Documentation.md` in your repository
- **Test Components**:
  - SSH: `ssh your_username@your_server_ip`
  - PHP: `php -v` on server  
  - Permissions: `ls -la /var/www/html`

## 🚀 What's Next?

- **Add More Plugins**: Create plugins in `local/`, `mod/`, or `blocks/` directories
- **Customize Workflows**: Modify `.github/workflows/` files as needed
- **Set Up Monitoring**: Configure notifications for deployment status
- **Database Backups**: Set up regular database backup procedures
- **SSL Certificate**: Configure HTTPS for your Moodle installation

---

**⏱️ Total Setup Time: ~15 minutes**  
**🎯 Result: Fully automated CI/CD pipeline for Moodle plugins**

*For advanced configuration and troubleshooting, see the [Complete Documentation](CI-CD-Documentation.md).* 