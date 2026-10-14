<?php
// Runs with container PHP on Ubuntu or Windows; never reads the local .env.
$target = $argv[1] ?? '/setup/.env.docker';
if (file_exists($target)) {
    fwrite(STDERR, "Docker environment already exists; refusing to overwrite keys or passwords.\n");
    exit(1);
}
$template = file_get_contents(__DIR__.'/environment.example');
$password = bin2hex(random_bytes(24));
$values = [
    'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)),
    'DB_PASSWORD' => $password,
    'MYSQL_PASSWORD' => $password,
    'MYSQL_ROOT_PASSWORD' => bin2hex(random_bytes(24)),
];
foreach ($values as $name => $value) {
    $template = preg_replace('/^'.preg_quote($name, '/').'=$/m', $name.'='.$value, $template);
}
umask(0077);
$handle = fopen($target, 'x');
if ($handle === false || fwrite($handle, $template) !== strlen($template)) {
    fwrite(STDERR, "Could not write Docker environment.\n");
    exit(1);
}
fclose($handle);
echo "Created .env.docker with a unique application key and database passwords. Values were not printed.\n";
