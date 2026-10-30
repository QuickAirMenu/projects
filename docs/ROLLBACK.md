# iProjects Rollback Procedure

## Quick Rollback (git revert)
```bash
ssh -p 65002 u227212075@185.214.124.218
cd /home/u227212075/domains/i-projects.net/[app_dir]
git log --oneline -5          # find the commit to revert to
git revert HEAD               # revert last commit
# OR
git reset --hard [commit_hash]  # force reset to specific commit
php artisan optimize:clear
php artisan optimize
```

## Database Rollback (from backup)
```bash
# Find latest backup
ls -la /home/u227212075/backups/iprojects/

# Restore (replace [DB_NAME] and [FILE])
gunzip < /home/u227212075/backups/iprojects/[FILE].sql.gz | mysql -u [DB_USER] -p"[PASS]" [DB_NAME]
```

## Full Rollback Steps
1. Identify the issue (check error logs: storage/logs/laravel.log)
2. Git revert to last known good commit
3. Restore database from backup if data corrupted
4. Clear caches: php artisan optimize:clear
5. Rebuild: php artisan optimize
6. Test: curl https://[subdomain].i-projects.net/health
