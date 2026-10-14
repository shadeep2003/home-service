<?php
// Run against the image without mounting host directories or injecting secrets.
chdir(dirname(__DIR__));
$forbidden = ['.env', '.env.docker', '.git', 'auth.json', 'bootstrap/cache/config.php'];
foreach ($forbidden as $path) {
    if (file_exists($path)) {
        fwrite(STDERR, "Unexpected sensitive/runtime file: $path\n");
        exit(1);
    }
}
foreach (['storage', 'public/uploads'] as $directory) {
    if (!is_dir($directory)) { continue; }
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($files as $file) {
        if ($file->isFile() && $file->getFilename() !== '.gitignore') {
            fwrite(STDERR, "Unexpected persisted data in image.\n");
            exit(1);
        }
    }
}
foreach (['APP_KEY', 'DB_PASSWORD', 'MYSQL_PASSWORD', 'MYSQL_ROOT_PASSWORD', 'MAIL_PASSWORD'] as $name) {
    if (getenv($name) !== false) {
        fwrite(STDERR, "Unexpected runtime secret variable in image environment: $name\n");
        exit(1);
    }
}
echo "Image checks passed: no local environment, cached configuration, uploads, logs, or runtime secret variables.\n";
