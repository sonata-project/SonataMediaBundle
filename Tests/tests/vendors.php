#!/usr/bin/env php
<?php

set_time_limit(0);

$vendorDir = __DIR__.'/../../vendor';
if (!is_dir($vendorDir)) {
  mkdir($vendorDir);
}

$deps = array(
    array('symfony', 'git://github.com/symfony/symfony.git', isset($_SERVER['SYMFONY_VERSION']) ? $_SERVER['SYMFONY_VERSION'] : 'origin/master'),
    array('knpmenu', 'git://github.com/KnpLabs/KnpMenu.git', 'origin/master'),
    array('Sonata/AdminBundle', 'git://github.com/sonata-project/SonataAdminBundle.git', 'origin/master'),
    array('gaufrette', 'git://github.com/KnpLabs/Gaufrette.git', 'origin/master'),
    array('buzz', 'git://github.com/kriswallsmith/Buzz.git', 'origin/master'),
    array('imagine', 'git://github.com/avalanche123/Imagine.git', 'origin/master')
);

foreach ($deps as $dep) {
    list($name, $url, $rev) = $dep;

    echo "> Installing/Updating $name\n";

    $installDir = $vendorDir.'/'.$name;
    if (!is_dir($installDir)) {
        system(sprintf('git clone --quiet %s %s', escapeshellarg($url), escapeshellarg($installDir)));
    }

    system(sprintf('cd %s && git fetch origin && git reset --hard %s', escapeshellarg($installDir), escapeshellarg($rev)));
}
