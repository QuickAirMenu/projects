<?php

// Backup trigger - callable via URL with secret key
// Usage: curl https://airmenu.i-projects.net/backup-trigger.php?key=BACKUP_SECRET_2026
$SECRET = 'BACKUP_SECRET_2026';
$key = $_GET['key'] ?? '';
if ($key !== $SECRET) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}
$output = shell_exec('/bin/bash /home/u227212075/backups/iprojects/backup-all.sh 2>&1');
echo $output;
