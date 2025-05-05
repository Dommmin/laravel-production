# VPS Server Setup Guide for Docker-based Laravel Deployment

This guide will help you set up your VPS for Laravel deployment with Docker, ensuring proper permissions and zero downtime.

## 0. Create User (optional - you can use your non root user)

```bash
# Create user with proper primary group
sudo adduser deployer --ingroup www-data
sudo usermod -aG sudo deployer

# Secure sudo access
echo "deployer ALL=(ALL:ALL) ALL" | sudo tee /etc/sudoers.d/deployer
echo 'Defaults:deployer !requiretty' | sudo tee -a /etc/sudoers.d/deployer  

# Fix home directory permissions
sudo chmod 711 /home/deployer
```

## 1. Initial Server Setup

```bash
# Update the system
sudo apt update
sudo apt upgrade -y

# Install Docker and Docker Compose
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Add deployer to docker group
sudo usermod -aG docker deployer

# Install UFW and configure
sudo apt install -y ufw
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw enable
```

## 2. Set Up Directory Structure

```bash
# Create main application directory
sudo mkdir -p /home/deployer/laravel
sudo chown -R deployer:www-data /home/deployer/laravel
sudo chmod -R 2775 /home/deployer/laravel

# Create required directories
sudo mkdir -p /home/deployer/laravel/storage/{app,framework,logs}
sudo mkdir -p /home/deployer/laravel/storage/framework/{cache,sessions,views}
sudo mkdir -p /home/deployer/laravel/bootstrap/cache
sudo mkdir -p /home/deployer/laravel/public/build

# Set proper permissions
sudo chown -R deployer:www-data /home/deployer/laravel/storage
sudo chown -R deployer:www-data /home/deployer/laravel/bootstrap/cache
sudo chown -R deployer:www-data /home/deployer/laravel/public

# Set directory permissions
sudo chmod -R 775 /home/deployer/laravel/storage
sudo chmod -R 775 /home/deployer/laravel/bootstrap/cache
sudo chmod -R 775 /home/deployer/laravel/public

# Set ACL for future files
sudo apt install -y acl
sudo setfacl -Rdm g:www-data:rwx /home/deployer/laravel
sudo setfacl -Rdm g:deployer:rwx /home/deployer/laravel

# Create docker directory
sudo mkdir -p /home/deployer/laravel/docker/nginx/conf.d
sudo mkdir -p /home/deployer/laravel/docker/php
sudo chown -R deployer:www-data /home/deployer/laravel/docker
sudo chmod -R 775 /home/deployer/laravel/docker
```

## 3. Configure Docker Permissions

```bash
# Create Docker network
docker network create laravel_network

# Create Docker volumes
docker volume create laravel_storage
docker volume create laravel_bootstrap_cache
docker volume create laravel_public
```

## 4. Set Up SSH Key for GitHub Actions

```bash
# Switch to deployer user
su - deployer

# Create SSH directory
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Generate SSH key
ssh-keygen -t rsa -b 4096 -C "github-actions-deploy"

# Add public key to authorized_keys
cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# Display the private key
cat ~/.ssh/id_rsa
```

## 5. Add GitHub Secrets

Add the following secrets to your GitHub repository:

- `SSH_HOST`: Your VPS IP address or domain
- `SSH_USER`: deployer
- `SSH_KEY`: The private SSH key generated above
- `SSH_PORT`: 22 (or your custom SSH port)

Add variable for .env production file:
- `ENV_FILE`: The contents of your .env file

## 6. Final Steps

1. Log out and log back in to apply group changes:
```bash
exit
# Log back in as deployer
```

2. Verify Docker access:
```bash
docker ps
```

3. Verify directory permissions:
```bash
ls -la /home/deployer/laravel
```

4. Push your code to the `main` branch to trigger the deployment.

## Troubleshooting

- **Permission Issues**: 
  ```bash
  # Check current user groups
  groups
  
  # Check directory permissions
  ls -la /home/deployer/laravel
  
  # Check Docker access
  docker ps
  ```

- **Docker Issues**:
  ```bash
  # Check Docker status
  sudo systemctl status docker
  
  # Check Docker logs
  sudo journalctl -fu docker
  ```

- **Deployment Failures**: Check the GitHub Actions logs for detailed error messages.

## Security Notes

1. Keep your system updated:
```bash
sudo apt update && sudo apt upgrade -y
```

2. Monitor system logs:
```bash
sudo tail -f /var/log/auth.log
sudo tail -f /var/log/syslog
```

3. Regular security audits:
```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

## Conclusion

After completing these steps, your server will be ready for Docker-based Laravel deployment. The setup ensures:
- Proper permissions for Docker and Laravel
- Secure SSH access for GitHub Actions
- Persistent storage for Laravel data
- Proper user and group configurations
- Security best practices implementation 