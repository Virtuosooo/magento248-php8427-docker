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
exit(is_resource($process) ? proc_close($process) : 1);
