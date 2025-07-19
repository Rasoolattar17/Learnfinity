# 🔐 GitHub Secrets Setup Guide

## Required Secrets for Deployment

### For First-Time Deployment (`FIRST_TIME_DEPLOYMENT=true`)

| Secret Name | Description | Example Value |
|-------------|-------------|---------------|
| `SERVER_HOST` | Your server hostname or IP address | `192.168.1.100` or `your-server.com` |
| `SERVER_USERNAME` | SSH username for server access | `ubuntu` or `root` |
| `SERVER_SSH_KEY` | Private SSH key for authentication | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `SERVER_BASE_PATH` | Base directory on server where Moodle will be installed | `/var/www` or `/home/ubuntu` |
| `FIRST_TIME_DEPLOYMENT` | Set to "true" for first deployment | `true` |

### For Plugin Updates (`FIRST_TIME_DEPLOYMENT=false`)

| Secret Name | Description | Example Value |
|-------------|-------------|---------------|
| `SERVER_HOST` | Your server hostname or IP address | `192.168.1.100` or `your-server.com` |
| `SERVER_USERNAME` | SSH username for server access | `ubuntu` or `root` |
| `SERVER_SSH_KEY` | Private SSH key for authentication | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `MOODLE_PATH` | Full path to existing Moodle installation | `/var/www/moodle` |
| `MOODLE_URL` | Your Moodle site URL | `https://your-moodle.com` |
| `FIRST_TIME_DEPLOYMENT` | Set to "false" for plugin updates | `false` |

## How to Configure Secrets

### Step 1: Go to GitHub Repository Settings
1. Navigate to your repository on GitHub
2. Click on **Settings** tab
3. In the left sidebar, click **Secrets and variables** → **Actions**

### Step 2: Add Each Secret
1. Click **New repository secret**
2. Enter the secret name (e.g., `SERVER_HOST`)
3. Enter the secret value
4. Click **Add secret**

### Step 3: Verify All Secrets
Make sure you have configured all required secrets based on your deployment type.

## SSH Key Setup

### Generate SSH Key (if needed):
```bash
ssh-keygen -t rsa -b 4096 -C "your-email@example.com"
```

### Add Public Key to Server:
```bash
# Copy public key to server
ssh-copy-id username@your-server.com

# Or manually add to ~/.ssh/authorized_keys
cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
```

### Use Private Key in GitHub Secret:
Copy the entire private key content (including `-----BEGIN` and `-----END` lines) to the `SERVER_SSH_KEY` secret.

## Testing Connection

### Test SSH Connection:
```bash
ssh username@your-server.com
```

### Verify Paths Exist:
```bash
# For first-time deployment
ls -la /var/www

# For plugin updates
ls -la /var/www/moodle
```

## Common Issues

### ❌ "No such file or directory"
- Check if `SERVER_BASE_PATH` or `MOODLE_PATH` is correct
- Ensure the path exists on the server
- Verify the user has access to the directory

### ❌ "Permission denied"
- Check SSH key configuration
- Verify username and host are correct
- Ensure SSH key is added to server's authorized_keys

### ❌ "Secret not found"
- Double-check secret names (case-sensitive)
- Ensure secrets are added to the correct repository
- Verify you're using the right deployment type

## Quick Setup Checklist

### For First-Time Deployment:
- [ ] `SERVER_HOST` configured
- [ ] `SERVER_USERNAME` configured  
- [ ] `SERVER_SSH_KEY` configured
- [ ] `SERVER_BASE_PATH` configured
- [ ] `FIRST_TIME_DEPLOYMENT` set to "true"
- [ ] SSH connection tested
- [ ] Base path exists and is accessible

### For Plugin Updates:
- [ ] `SERVER_HOST` configured
- [ ] `SERVER_USERNAME` configured
- [ ] `SERVER_SSH_KEY` configured
- [ ] `MOODLE_PATH` configured
- [ ] `MOODLE_URL` configured
- [ ] `FIRST_TIME_DEPLOYMENT` set to "false"
- [ ] SSH connection tested
- [ ] Moodle installation exists at specified path

## Security Notes

⚠️ **Important Security Considerations:**
- Never commit secrets to your repository
- Use strong, unique SSH keys
- Regularly rotate SSH keys
- Limit SSH key permissions on server
- Use specific user accounts (not root) when possible
- Consider using SSH key passphrases for additional security

---

**Need Help?** Check the deployment logs in GitHub Actions for specific error messages. 