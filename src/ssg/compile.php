<?php

use Attitude\PHPX\Compiler\Compiler;

require_once 'vendor/autoload.php';
require_once 'src/types/index.php';
require_once 'getSiteConfig.php';
require_once 'globMapRecursive.php';

$compiler = new Compiler();

// TODO: Allow to pass the path to the site config file
$config = getSiteConfig('site.ini');

globMapRecursive("{$config->COMPONENTS_PATH}/*.phpx", function ($file) use ($compiler) {
  $contents = file_get_contents($file);
  $compiler->compile($contents);

  file_put_contents(substr($file, 0, -1), $compiler->getCompiled());
});
