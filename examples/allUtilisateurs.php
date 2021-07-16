<?php

use PierreGranger\ApidaeException;
use PierreGranger\ApidaeMembres;

if (php_sapi_name() != 'cli') die('cli only');

require(realpath(dirname(__FILE__)) . '/../vendor/autoload.php');
require(realpath(dirname(__FILE__)) . '/../config.inc.php');

$apidaeMembres = new ApidaeMembres(array_merge(
    $configApidaeMembres,
    ['debug' => true, 'timer' => true]
));

$apidaeMembres->start('getAllUtilisateurs');
try {
    $result = $apidaeMembres->getAllUtilisateurs();
} catch (ApidaeException $e) {
    print_r($e->getDetails());
    die();
} catch (Exception $e) {
    print_r($e);
    die();
}
$apidaeMembres->stop('getMembres');

$apidaeMembres->timer();
