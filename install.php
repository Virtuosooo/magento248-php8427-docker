<?php
// 每个部署独立安装；已安装时拒绝重复执行，避免覆盖数据。
if (file_exists('/var/www/html/app/etc/env.php')) {
    fwrite(STDERR, "Magento already installed; refusing to reinstall.\n");
    exit(1);
}
$args = [PHP_BINARY, 'bin/magento', 'setup:install',
 '--base-url=https://localhost:8448/', '--base-url-secure=https://localhost:8448/',
 '--use-secure=1', '--use-secure-admin=1', '--use-rewrites=1',
 '--db-host=db', '--db-name=magento', '--db-user=magento',
 '--db-password=' . getenv('DB_PASSWORD'),
 '--backend-frontname=admin', '--admin-firstname=Recon', '--admin-lastname=Test',
 '--admin-email=recon-admin@example.test', '--admin-user=recon_admin',
 '--admin-password=' . getenv('ADMIN_PASSWORD'),
 '--language=en_US', '--currency=HKD', '--timezone=Asia/Hong_Kong',
 '--search-engine=opensearch', '--opensearch-host=opensearch',
 '--opensearch-port=9200', '--session-save=files', '--no-interaction'];
$process = proc_open($args, [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes, '/var/www/html');
$status = is_resource($process) ? proc_close($process) : 1;
if ($status !== 0) { exit($status); }
// 本机无邮件服务：每次部署独立初始化 Google 2FA，保留后台双重验证。
$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
$secret = '';
for ($i = 0; $i < 32; $i++) { $secret .= $alphabet[random_int(0, 31)]; }
file_put_contents('/var/www/html/var/admin-tfa-secret.txt', $secret);
chmod('/var/www/html/var/admin-tfa-secret.txt', 0600);
foreach ([
 ['config:set', 'twofactorauth/general/force_providers', 'google'],
 ['security:tfa:google:set-secret', 'recon_admin', $secret],
] as $command) {
    $process = proc_open(array_merge([PHP_BINARY, 'bin/magento'], $command),
        [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes, '/var/www/html');
    $status = is_resource($process) ? proc_close($process) : 1;
    if ($status !== 0) { exit($status); }
}
echo "Independent Google 2FA secret saved in var/admin-tfa-secret.txt.\n";
exit(0);
