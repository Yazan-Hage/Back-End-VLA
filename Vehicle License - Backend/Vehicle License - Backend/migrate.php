<?php
// migrate.php
foreach (glob(__DIR__ . '/migrations/*.php') as $file) {
    require $file;
}
echo "All migrations executed successfully.\n";
