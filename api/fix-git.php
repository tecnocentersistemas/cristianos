<?php
// One-time fix: mark directory as safe for git
echo "<pre>";
echo shell_exec('git config --global --add safe.directory /var/www/cristianos 2>&1');
echo "Fixed safe.directory\n";
echo shell_exec('cd /var/www/cristianos && git pull origin main 2>&1');
echo "</pre>";
