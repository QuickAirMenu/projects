# iProjects Deployment Guide

## Automated Deploy (GitHub Actions)
Push to master branch triggers:
1. Tests workflow (Pint + PHPStan + PHPUnit)
2. Deploy workflow (matrix: 4 instances via rsync + SSH)

## Manual Deploy (deploy-all.sh)
```bash
./deploy-all.sh
```
This script:
1. SSH to each instance
2. git pull origin master
3. composer install --no-dev -o
4. php artisan migrate --force
5. php artisan optimize

## Individual Instance Deploy
```bash
ssh -p 65002 u227212075@185.214.124.218
cd /home/u227212075/domains/i-projects.net/[app_dir]
git pull origin master
composer install --no-dev -o
php artisan migrate --force
php artisan optimize
php artisan view:clear
```

## GitHub Secrets Required
- SSH_PRIVATE_KEY: Private key content
- SSH_HOST: 185.214.124.218
- SSH_PORT: 65002
- SSH_USER: u227212075
- SSH_PATH: (per instance in matrix)

## Cron Jobs (set in hPanel → Advanced → Cron Jobs)
```
0 3 * * * /bin/bash /home/u227212075/backups/iprojects/backup-all.sh >> /home/u227212075/backups/iprojects/cron.log 2>&1
```
