# Learnfinity CI/CD System Documentation

## 🎯 Overview

The Learnfinity project uses a sophisticated **CI/CD pipeline** that automatically tests Moodle plugins and deploys them to production servers. The system consists of two main workflows that work together to ensure code quality and reliable deployments.

## 📋 Table of Contents

1. [Workflow Architecture](#workflow-architecture)
2. [Plugin Testing Workflow (learnfinity-ci.yml)](#plugin-testing-workflow)
3. [Deployment Workflow (deploy.yml)](#deployment-workflow)
4. [Configuration & Setup](#configuration--setup)
5. [Usage Guide](#usage-guide)
6. [Troubleshooting](#troubleshooting)
7. [Best Practices](#best-practices)

---

## 🏗️ Workflow Architecture

```mermaid
graph TD
    A[Developer Pushes Code] --> B[Learnfinity Plugin CI]
    B --> C{All Tests Pass?}
    C -->|✅ Yes| D[Deploy Workflow Triggered]
    C -->|❌ No| E[Deployment Blocked]
    D --> F[First-Time Deploy or Plugin Update]
    F --> G[Server Deployment]
    G --> H[Verification & Notification]
    
    I[Manual Trigger] --> D
    J[Emergency Deploy] --> D
```

### 🔄 Pipeline Flow

1. **Code Push** → **Plugin Testing** → **Deployment** → **Verification**
2. **Parallel Execution**: Multiple PHP/Moodle versions tested simultaneously  
3. **Quality Gates**: Deployment only proceeds if all tests pass
4. **Fallback Options**: Manual and emergency deployment triggers available

---

## 🧪 Plugin Testing Workflow (learnfinity-ci.yml)

### 🎯 Purpose
Automatically detects and tests Moodle plugins for code quality, standards compliance, and functionality across multiple PHP and Moodle versions.

### ⚡ Key Features

#### **🔍 Dynamic Plugin Detection**
- **Auto-Discovery**: Automatically finds plugins in `local/`, `mod/`, and `blocks/` directories
- **Smart Filtering**: Ignores built-in Moodle plugins, focuses on custom developments
- **Manual Override**: Allows specifying exact plugin path via manual trigger

#### **🏗️ Multi-Matrix Testing**
```yaml
PHP Versions: 8.1, 8.2, 8.3
Moodle Versions: MOODLE_403_STABLE, MOODLE_404_STABLE, MOODLE_405_STABLE
Database: MySQL 8.4
```

#### **📊 Comprehensive Test Suite**
1. **PHP Lint** - Syntax validation
2. **PHP Mess Detector** - Code quality analysis  
3. **Moodle Code Checker** - Coding standards compliance
4. **PHPDoc Checker** - Documentation standards
5. **Plugin Validation** - Moodle-specific plugin validation
6. **Savepoints Check** - Database upgrade validation
7. **Grunt Tasks** - JavaScript/CSS validation
8. **PHPUnit Tests** - Unit test execution
9. **Behat Tests** - Functional test execution

### 🚀 Triggers

| Trigger Type | Condition | Use Case |
|--------------|-----------|----------|
| **Automatic** | Push to `master` branch | Normal development workflow |
| **Automatic** | Pull Request to `master` | Code review process |
| **Manual** | `workflow_dispatch` | On-demand testing |

### 🔧 Configuration

```yaml
# Manual trigger with custom plugin path
inputs:
  plugin_path:
    description: 'Plugin path to test (e.g., local/myplugin). Leave empty to auto-detect.'
    required: false
    type: string
```

---

## 🚀 Deployment Workflow (deploy.yml)

### 🎯 Purpose
Deploys Moodle installations and plugin updates to production servers with automated verification and rollback capabilities.

### ⚡ Key Features

#### **🔀 Dual Deployment Modes**

##### **1. First-Time Moodle Deployment**
- **Complete Moodle Installation**: Clones entire Moodle codebase
- **Directory Structure Setup**: Creates `moodle/` and `moodledata/` directories
- **Permission Configuration**: Sets proper file/folder permissions
- **Configuration Setup**: Creates `config.php` from `config-dist.php`
- **Backup Integration**: Backs up existing installations

##### **2. Plugin Update Deployment** 
- **Incremental Updates**: Updates specific plugins only
- **Git Synchronization**: Pulls latest changes from repository
- **Database Upgrades**: Runs Moodle upgrade scripts
- **Cache Management**: Clears Moodle caches automatically
- **Plugin Verification**: Validates plugin installation

#### **🛡️ Smart Trigger System**

| Trigger Type | Condition | Security Level |
|--------------|-----------|----------------|
| **Post-CI** | After successful CI tests | 🔒 **High** - Full testing |
| **Manual** | `workflow_dispatch` | 🔧 **Medium** - Admin control |
| **Emergency** | Deploy workflow changes | 🚨 **Low** - Bypass CI |

#### **✅ Built-in Verification**
- **File Integrity**: Verifies all required files exist
- **Permission Validation**: Checks file/folder permissions
- **Database Connection**: Tests Moodle configuration
- **Plugin Status**: Validates plugin installation

### 🔧 Configuration Requirements

#### **First-Time Deployment Secrets**
```bash
FIRST_TIME_DEPLOYMENT=true          # Enables first-time mode
SERVER_HOST=your.server.com         # Server hostname/IP    
SERVER_USERNAME=your_ssh_user       # SSH username
SERVER_SSH_KEY=-----BEGIN RSA---    # SSH private key
SERVER_BASE_PATH=/var/www/html      # Installation directory
SERVER_PORT=22                      # SSH port (optional)
```

#### **Plugin Update Secrets**
```bash
FIRST_TIME_DEPLOYMENT=false         # Enables update mode  
SERVER_HOST=your.server.com         # Server hostname/IP
SERVER_USERNAME=your_ssh_user       # SSH username
SERVER_SSH_KEY=-----BEGIN RSA---    # SSH private key
MOODLE_PATH=/var/www/html/moodle    # Existing Moodle path
SERVER_PORT=22                      # SSH port (optional)
```

---

## ⚙️ Configuration & Setup

### 🔑 1. GitHub Secrets Setup

Navigate to: **Repository → Settings → Secrets and variables → Actions**

#### **Required for All Deployments:**
```bash
SERVER_HOST         # Your server IP or domain name
SERVER_USERNAME     # SSH username (e.g., 'azureuser')  
SERVER_SSH_KEY      # Private SSH key content
SERVER_PORT         # SSH port (default: 22)
```

#### **For First-Time Deployment:**
```bash
FIRST_TIME_DEPLOYMENT=true
SERVER_BASE_PATH=/var/www/html      # Where to install Moodle
```

#### **For Plugin Updates:**
```bash  
FIRST_TIME_DEPLOYMENT=false
MOODLE_PATH=/var/www/html/moodle    # Existing Moodle installation
```

### 🖥️ 2. Server Prerequisites

#### **Software Requirements:**
- **Operating System**: Ubuntu 20.04+ or CentOS 7+
- **Web Server**: Nginx or Apache
- **PHP**: 8.1, 8.2, or 8.3 with required extensions
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Git**: Latest version

#### **PHP Extensions:**
```bash
sudo apt install php8.3-{mbstring,xml,intl,gd,curl,zip,mysqli,pgsql,soap,redis}
```

#### **Directory Permissions:**
```bash
# Ensure SSH user can write to web directory
sudo chown -R your_ssh_user:www-data /var/www/html
sudo chmod -R 775 /var/www/html
```

### 🔐 3. SSH Key Setup

#### **Generate SSH Key (if needed):**
```bash
ssh-keygen -t rsa -b 4096 -C "github-deploy-key"
```

#### **Add Public Key to Server:**
```bash
# On your server
echo "your_public_key_content" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

#### **Add Private Key to GitHub:**
Copy the **entire private key** (including `-----BEGIN RSA PRIVATE KEY-----` and `-----END RSA PRIVATE KEY-----`) to the `SERVER_SSH_KEY` secret.

---

## 📖 Usage Guide

### 🚀 Normal Development Workflow

#### **1. Develop Your Plugin**
```bash
# Create plugin in local directory
mkdir local/myplugin
# ... develop your plugin files
```

#### **2. Commit & Push**
```bash
git add .
git commit -m "Add new plugin features"
git push origin master
```

#### **3. Automatic Process**
1. **CI Tests Run** → Plugin automatically detected and tested
2. **Tests Pass** → Deployment automatically triggered  
3. **Deployment Completes** → Plugin available on server

### 🔧 Manual Operations

#### **Manual Testing Only**
```bash
# Go to Actions tab → "Learnfinity Plugin CI" → "Run workflow"
# Optionally specify plugin path: "local/myplugin"
```

#### **Manual Deployment**
```bash  
# Go to Actions tab → "Deploy Moodle" → "Run workflow"
```

#### **Emergency Deployment (Bypass CI)**
```bash
# Make any change to .github/workflows/deploy.yml
git add .github/workflows/deploy.yml
git commit -m "Emergency deployment trigger" 
git push origin master
```

### 📊 Monitoring Workflows

#### **GitHub Actions Dashboard**
1. **Repository** → **Actions tab**
2. **View running/completed workflows**
3. **Click workflow for detailed logs**
4. **Download artifacts** (test reports, screenshots)

#### **Workflow Status Badges**
Add to your README.md:
```markdown
![CI Status](https://github.com/username/repository/workflows/Learnfinity%20Plugin%20CI/badge.svg)
![Deploy Status](https://github.com/username/repository/workflows/Deploy%20Moodle/badge.svg)
```

---

## 🔧 Troubleshooting

### ❌ Common Issues & Solutions

#### **1. CI Tests Failing**

**Problem**: PHP Coding Standards violations
```bash
Solution:
- Run locally: vendor/bin/phpcs --standard=moodle /path/to/plugin
- Fix coding standards issues
- Commit and push fixes
```

**Problem**: PHPUnit tests failing
```bash
Solution:  
- Check test logic in tests/ directory
- Verify test data and assertions
- Run tests locally: vendor/bin/phpunit /path/to/plugin/tests
```

#### **2. Deployment Issues**

**Problem**: SSH connection failed
```bash
Solution:
- Verify SERVER_HOST, SERVER_USERNAME, SERVER_SSH_KEY secrets
- Test SSH connection manually: ssh user@host
- Check server SSH service: sudo systemctl status ssh
```

**Problem**: Permission denied during deployment
```bash
Solution:
- Fix server permissions: sudo chown -R user:www-data /var/www/html
- Set proper permissions: sudo chmod -R 775 /var/www/html
- Verify SSH user has sudo access (if needed)
```

**Problem**: Plugin not detected
```bash
Solution:
- Ensure plugin has version.php file
- Check plugin has lang/en/pluginname.php file
- Verify proper directory structure: local/pluginname/
```

#### **3. Moodle Installation Issues**

**Problem**: config.php database connection fails
```bash
Solution:
- Update config.php with correct database credentials
- Test database connection manually
- Check database server is running
```

**Problem**: Moodle upgrade fails
```bash
Solution:
- Check admin/cli/upgrade.php output
- Verify database permissions
- Check Moodle logs in moodledata/
```

### 📋 Debug Checklist

```markdown
☐ GitHub Secrets configured correctly
☐ SSH key has proper format and permissions  
☐ Server has required software installed
☐ Directory permissions set correctly
☐ Plugin follows Moodle standards
☐ Database credentials are correct
☐ Web server configuration is proper
```

---

## 🏆 Best Practices

### 📝 Development Guidelines

#### **1. Plugin Structure**
```
local/myplugin/
├── version.php              # ✅ Required - Plugin metadata
├── lib.php                  # ✅ Required - Core functions
├── lang/en/local_myplugin.php # ✅ Required - Language strings
├── settings.php             # 🔧 Optional - Admin settings
├── index.php                # 🔧 Optional - Main page
├── tests/                   # 🧪 Recommended - Unit tests
│   └── myplugin_test.php    
├── classes/                 # 🏗️ Recommended - OOP structure
└── README.md                # 📖 Recommended - Documentation
```

#### **2. Coding Standards**
```php
// ✅ Always include Moodle header
<?php
// This file is part of Moodle - http://moodle.org/

// ✅ Proper PHPDoc comments
/**
 * Description of function
 * 
 * @param string $param Description
 * @return bool Success status
 */
function my_function($param) {
    // ✅ Follow Moodle coding standards
    global $DB, $USER;
    // Implementation
}
```

#### **3. Testing Strategy**
```php
// ✅ Write comprehensive unit tests
class myplugin_test extends advanced_testcase {
    
    public function setUp(): void {
        $this->resetAfterTest(true);
    }
    
    public function test_my_function() {
        // Test implementation
        $this->assertTrue(my_function('test'));
    }
}
```

### 🚀 Deployment Guidelines

#### **1. Environment Separation**
```bash
# Development
FIRST_TIME_DEPLOYMENT=true
SERVER_BASE_PATH=/var/www/dev

# Production  
FIRST_TIME_DEPLOYMENT=false
MOODLE_PATH=/var/www/html/moodle
```

#### **2. Rollback Strategy**
- **Automated Backups**: Every deployment creates timestamped backups
- **Manual Rollback**: SSH to server and restore from backup
- **Database Backups**: Regular automated database backups
- **Version Control**: Use Git tags for release management

#### **3. Monitoring & Alerts**
```yaml
# Set up GitHub notifications
- Email notifications for workflow failures
- Slack/Teams integration for deployment status
- Server monitoring for performance metrics
```

### 🔒 Security Best Practices

#### **1. SSH Security**
```bash
# Use dedicated deploy key (not personal SSH key)
ssh-keygen -t rsa -b 4096 -C "deploy-key-learnfinity" -f deploy_key

# Restrict SSH key permissions on server
echo 'command="cd /var/www && $SSH_ORIGINAL_COMMAND" ssh-rsa AAAAB...' >> ~/.ssh/authorized_keys
```

#### **2. Server Security**
```bash
# Disable password authentication
sudo sed -i 's/PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config

# Use fail2ban for brute force protection
sudo apt install fail2ban

# Regular security updates
sudo apt update && sudo apt upgrade
```

#### **3. Moodle Security**
```php
// config.php security settings
$CFG->forcessl = true;                    // Force HTTPS
$CFG->cookiesecure = true;                // Secure cookies
$CFG->preventexecpath = true;             // Prevent execution
$CFG->disableupdateautodeploy = true;     // Disable auto-updates
```

---

## 📞 Support & Contributing

### 🆘 Getting Help
- **GitHub Issues**: Report bugs and request features
- **Documentation**: Check this guide and inline comments
- **Community**: Moodle Developer Forums

### 🤝 Contributing
1. **Fork the repository**
2. **Create feature branch**: `git checkout -b feature/amazing-feature`
3. **Follow coding standards**: Use the CI tests to validate
4. **Write tests**: Add appropriate test coverage
5. **Submit Pull Request**: CI will automatically test your changes

### 📚 Additional Resources
- **Moodle Developer Documentation**: https://docs.moodle.org/dev/
- **PHP Standards**: https://www.php-fig.org/psr/
- **GitHub Actions**: https://docs.github.com/en/actions

---

## 🏷️ Version Information

- **Documentation Version**: 1.0
- **Compatible with**: Moodle 4.0+ 
- **Last Updated**: January 2025
- **Maintained by**: Learnfinity Development Team

---

*This documentation is automatically updated with each release. For the latest version, check the repository's docs/ directory.* 