#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_VERSION_ID < 80500) { fwrite(STDERR,"PHP 8.5+ recomendado; detectado ".PHP_VERSION."\n"); exit(2); }
$lock=__DIR__.'/../composer.lock';
if(!is_file($lock)){echo "composer.lock não existe. Execute composer update/install antes do deploy.\n";exit(3);}
$data=json_decode((string)file_get_contents($lock),true,512,JSON_THROW_ON_ERROR);
$versions=[];
foreach(($data['packages']??[]) as $p)$versions[$p['name']]=$p['version'];
foreach(['phpseclib/phpseclib','phpmailer/phpmailer'] as $pkg) echo $pkg.': '.($versions[$pkg]??'MISSING')."\n";
