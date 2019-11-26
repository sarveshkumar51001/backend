<?php

$dir = __DIR__ . '/../../../app';

$iterator = Symfony\Component\Finder\Finder::create()
    ->files()
    ->name('*.php')
    ->in($dir);

$options = [
    'theme'                => 'default',
    'title'                => 'Backend APP Code Documentation',
    'build_dir'            => __DIR__ . '/../../../public/docs/sami',
    'cache_dir'            => __DIR__ . '/../../../public/docs/sami',
];

$sami = new Sami\Sami($iterator, $options);

return $sami;
