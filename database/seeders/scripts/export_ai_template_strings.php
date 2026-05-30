<?php

declare(strict_types=1);

$path = dirname(__DIR__).'/data/ai_templates.json';
$items = json_decode((string) file_get_contents($path), true);
$unique = [];

foreach ($items as $item) {
  $unique[$item['content']] = ($unique[$item['content']] ?? 0) + 1;
}

echo 'total='.count($items).' unique_content='.count($unique).PHP_EOL;
