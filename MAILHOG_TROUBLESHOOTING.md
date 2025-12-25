# MailHog Email Testing Troubleshooting Guide

## Quick Fix for "Email Not Received" Issue

If emails aren't showing up in MailHog on a different machine, follow these steps:

### 1. Rebuild Docker Containers

```bash
# Stop all containers
docker-compose down

# Rebuild PHP container (important!)
docker-compose build --no-cache php

# Start everything
docker-compose up -d
```

### 2. Verify MailHog is Running

```bash
# Check if MailHog container is running
docker-compose ps mailhog

# Check MailHog logs
docker-compose logs mailhog

# Access MailHog web interface
# Open: http://localhost:8025
```

### 3. Test Email Configuration

```bash
# Check PHP container can reach MailHog
docker-compose exec php ping -c 3 mailhog

# Check msmtp configuration
docker-compose exec php cat /etc/msmtprc

# Check msmtp logs
docker-compose exec php cat /tmp/msmtp.log

# Test sending email directly
docker-compose exec php sh -c 'echo "Test email" | msmtp -v test@example.com'
```

### 4. Verify Network Connectivity

```bash
# Check if containers are on the same network
docker network inspect musicplayer.github.io_musicplayer_network

# Test port connectivity
docker-compose exec php nc -zv mailhog 1025
```

## Common Issues and Solutions

### Issue 1: Container Name Mismatch
**Symptom:** PHP can't connect to MailHog
**Solution:** The entrypoint.sh now uses the service name `mailhog` instead of container name

### Issue 2: MailHog Not Started Before PHP
**Symptom:** PHP starts before MailHog is ready
**Solution:** Added `depends_on: mailhog` in docker-compose.yml

### Issue 3: Cached Docker Build
**Symptom:** Old configuration still being used
**Solution:** Rebuild with `--no-cache` flag

### Issue 4: Port Conflicts
**Symptom:** MailHog ports already in use
**Solution:** 
```bash
# Check what's using the ports
lsof -i :1025
lsof -i :8025

# Stop conflicting services or change ports in docker-compose.yml
```

### Issue 5: Firewall Blocking
**Symptom:** Can't access MailHog web interface
**Solution:** Check firewall settings and allow ports 1025 and 8025

## Manual Email Test

Create a test PHP file to verify email sending:

```php
<?php
// test-email.php
$to = "test@example.com";
$subject = "Test Email";
$message = "This is a test email from Music Player";
$headers = "From: noreply@musicplayer.local\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully!\n";
    echo "Check MailHog at http://localhost:8025\n";
} else {
    echo "Email failed to send!\n";
    echo "Check logs: docker-compose logs php\n";
}
?>
```

Run it:
```bash
docker-compose exec php php /var/www/html/test-email.php
```

## Debugging Steps

### 1. Check PHP mail() function
```bash
docker-compose exec php php -r "var_dump(mail('test@test.com', 'Test', 'Body'));"
```

### 2. Check msmtp directly
```bash
docker-compose exec php sh -c 'echo -e "Subject: Test\n\nTest body" | msmtp -v test@example.com'
```

### 3. Check PHP error logs
```bash
docker-compose logs php | grep -i mail
docker-compose logs php | grep -i error
```

### 4. Verify environment variables
```bash
docker-compose exec php env | grep MAILHOG
```

## Expected Configuration

After the fix, your configuration should be:

**docker-compose.yml:**
- PHP service has `depends_on: mailhog`
- Environment variables: `MAILHOG_HOST=mailhog`, `MAILHOG_PORT=1025`

**entrypoint.sh:**
- Uses environment variables for MailHog host/port
- Defaults to `mailhog:1025`
- Creates msmtp log file at `/tmp/msmtp.log`

**Dockerfile:**
- Uses service name `mailhog` instead of container name
- Configures PHP to use msmtp for mail()

## Verification Checklist

- [ ] MailHog container is running: `docker-compose ps mailhog`
- [ ] MailHog web UI accessible: http://localhost:8025
- [ ] PHP can ping MailHog: `docker-compose exec php ping -c 3 mailhog`
- [ ] msmtp configured correctly: `docker-compose exec php cat /etc/msmtprc`
- [ ] Test email sent successfully
- [ ] Email appears in MailHog web interface

## Still Not Working?

1. **Check Docker network:**
   ```bash
   docker network ls
   docker network inspect musicplayer.github.io_musicplayer_network
   ```

2. **Restart everything:**
   ```bash
   docker-compose down -v
   docker-compose build --no-cache
   docker-compose up -d
   ```

3. **Check system resources:**
   ```bash
   docker stats
   ```

4. **View all logs:**
   ```bash
   docker-compose logs -f
   ```

## Contact Support

If none of these solutions work, provide:
- Output of `docker-compose ps`
- Output of `docker-compose logs php`
- Output of `docker-compose logs mailhog`
- Contents of `/tmp/msmtp.log` from PHP container
