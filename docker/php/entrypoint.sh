#!/bin/sh

# Get MailHog configuration from environment or use defaults
MAILHOG_HOST=${MAILHOG_HOST:-mailhog}
MAILHOG_PORT=${MAILHOG_PORT:-1025}

# Create msmtp configuration
cat > /etc/msmtprc << EOF
account default
host ${MAILHOG_HOST}
port ${MAILHOG_PORT}
from noreply@musicplayer.local
tls off
auto_from on
logfile /tmp/msmtp.log
EOF

chmod 644 /etc/msmtprc

echo "MailHog configured: ${MAILHOG_HOST}:${MAILHOG_PORT}"

# Start PHP-FPM
exec php-fpm