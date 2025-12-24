#!/bin/sh

# Create msmtp configuration
cat > /etc/msmtprc << EOF
account default
host musicplayer_mailhog
port 1025
from noreply@musicplayer.local
tls off
auto_from on
EOF

chmod 644 /etc/msmtprc

# Start PHP-FPM
exec php-fpm
