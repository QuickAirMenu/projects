<?php

return [
    'path' => env('BACKUP_PATH', '/home/u227212075/backups/iprojects'),
    'script' => env('BACKUP_SCRIPT', '/home/u227212075/backups/iprojects/backup-all.sh'),
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
];
