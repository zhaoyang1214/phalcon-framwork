<?php
use Phalcon\Loader;

$loader = new Loader();

$loader->registerDirs([
    BASE_PATH . 'cli/tasks/'
])->register();