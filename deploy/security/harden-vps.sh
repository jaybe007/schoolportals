#!/usr/bin/env bash
# ==============================================================================
# EduSphere SaaS - Complete Automated VPS Hardening Script
# Target OS: Ubuntu 22.04 LTS / Ubuntu 24.04 LTS
# Run as: root (e.g., sudo bash harden-vps.sh)
# ==============================================================================

set -euo pipefail

echo "=========================================================="
echo " Starting EduSphere SaaS Linux VPS Hardening Sequence"
echo "=========================================================="

# 1. Update OS and Packages
echo "[1/8] Updating package indices and applying security patches..."
apt update && apt upgrade -y
apt install -y ufw fail2ban unattended-upgrades apt-listchanges curl git rsync htop

# 2. Automated Unattended Security Upgrades
echo "[2/8] Enabling automated security updates..."
cat << 'EOF' > /etc/apt/apt.conf.d/50unattended-upgrades
Unattended-Upgrade::Allowed-Origins {
    "${distro_id}:${distro_codename}-security";
};
Unattended-Upgrade::AutoFixInterruptedDpkg "true";
Unattended-Upgrade::MinimalSteps "true";
Unattended-Upgrade::InstallOnShutdown "false";
Unattended-Upgrade::Remove-Unused-Kernel-Packages "true";
Unattended-Upgrade::Remove-New-Unused-Dependencies "true";
Unattended-Upgrade::Automatic-Reboot "false";
EOF

cat << 'EOF' > /etc/apt/apt.conf.d/20auto-upgrades
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";
APT::Periodic::AutocleanInterval "7";
EOF

# 3. Kernel & Network Stack Hardening (sysctl)
echo "[3/8] Hardening Linux kernel parameters (sysctl)..."
cat << 'EOF' > /etc/sysctl.d/99-security.conf
# Ignore ICMP echo broadcasts
net.ipv4.icmp_echo_ignore_broadcasts = 1

# Ignore bogus ICMP errors
net.ipv4.icmp_ignore_bogus_error_responses = 1

# Enable SYN Cookies to prevent SYN Flood attacks
net.ipv4.tcp_syncookies = 1

# Disable IP Source Routing
net.ipv4.conf.all.accept_source_route = 0
net.ipv4.conf.default.accept_source_route = 0

# Disable ICMP redirect acceptance
net.ipv4.conf.all.accept_redirects = 0
net.ipv4.conf.default.accept_redirects = 0
net.ipv4.conf.all.secure_redirects = 0
net.ipv4.conf.default.secure_redirects = 0

# Prevent packet forwarding
net.ipv4.ip_forward = 0

# Log suspicious / spoofed packets
net.ipv4.conf.all.log_martians = 1
net.ipv4.conf.default.log_martians = 1
EOF
sysctl -p /etc/sysctl.d/99-security.conf

# 4. SSH Hardening
echo "[4/8] Hardening SSH configuration..."
cp /etc/ssh/sshd_config /etc/ssh/sshd_config.bak
sed -i 's/^#*PermitRootLogin.*/PermitRootLogin no/' /etc/ssh/sshd_config
sed -i 's/^#*PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config
sed -i 's/^#*PubkeyAuthentication.*/PubkeyAuthentication yes/' /etc/ssh/sshd_config
sed -i 's/^#*X11Forwarding.*/X11Forwarding no/' /etc/ssh/sshd_config
sed -i 's/^#*MaxAuthTries.*/MaxAuthTries 3/' /etc/ssh/sshd_config
systemctl restart ssh

# 5. Firewall Configuration (UFW)
echo "[5/8] Configuring UFW firewall rules..."
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp comment 'SSH'
ufw allow 80/tcp comment 'HTTP'
ufw allow 443/tcp comment 'HTTPS'
ufw --force enable

# 6. Fail2ban Intrusion Prevention
echo "[6/8] Activating Fail2ban intrusion prevention..."
systemctl enable fail2ban
systemctl restart fail2ban

# 7. Secure Shared Memory (/run/shm)
echo "[7/8] Securing shared memory..."
if ! grep -q "/run/shm" /etc/fstab; then
    echo "tmpfs     /run/shm     tmpfs     defaults,noexec,nosuid,nodev     0     0" >> /etc/fstab
fi

# 8. Web Directory Permissions Hardening
echo "[8/8] Enforcing strict webroot file permissions..."
APP_PATH="/var/www/schoolportals"
if [ -d "$APP_PATH" ]; then
    chown -R deployer:www-data "$APP_PATH"
    find "$APP_PATH" -type d -exec chmod 755 {} \;
    find "$APP_PATH" -type f -exec chmod 644 {} \;
    chmod 600 "$APP_PATH/.env" 2>/dev/null || true
fi

echo "=========================================================="
echo " VPS Hardening Complete! System is production-hardened."
echo "=========================================================="
