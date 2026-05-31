<?php
$json = json_decode(file_get_contents(__DIR__ . '/.mlhub_adminfaker_tmp.json'), true, 512, JSON_THROW_ON_ERROR);
$body = var_export($json, true);
$header = <<<'HDR'
<?php

/**
 * Admin Faker — demo investor Đà Nẵng (SOHO / hộ kinh doanh).
 * Chỉnh file này rồi: php artisan admin-faker:refresh
 *
 * DISCLAIMER: Tên thương hiệu minh họa UI — không đại diện thương hiệu thật.
 */
return 

HDR;
file_put_contents(__DIR__ . '/mlhub_adminfaker_dn_soho.php', $header . $body . ';' . PHP_EOL);
unlink(__DIR__ . '/.mlhub_adminfaker_tmp.json');
echo 'written';
