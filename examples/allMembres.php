<?php

require(realpath(dirname(__FILE__)) . '/../vendor/autoload.php');
require(realpath(dirname(__FILE__)) . '/../config.inc.php');

$apidaeMembres = new \PierreGranger\ApidaeMembres(array_merge(
    $configApidaeMembres,
    array('debug' => true, 'timer' => true)
));

echo '<script src="//code.jquery.com/jquery-1.12.2.min.js"></script>' . PHP_EOL;
echo '<script src="./jquery.beautify-json.js"></script>' . PHP_EOL;
echo '<link rel="stylesheet" type="text/css" href="./beautify-json.css">' . PHP_EOL;

echo '<h1>tests recherche membres</h1>';

try {

    $apidaeMembres->start('getMembres');

    $filter = ['types' => ['Contributeur Généraliste']];
    $responsefields = [];

    $result = $apidaeMembres->getMembres($filter, $responsefields);
    echo '<pre>$ApidaeMembres->getMembres(' . json_encode($filter) . ',' . json_encode($responsefields) . ') ;</pre>';
    echo '<pre data-type="json">' . json_encode($result) . '</pre>' . PHP_EOL;
    $apidaeMembres->stop('getMembres');
} catch (Exception $e) {
    print_r($e);
    die();
}

$apidaeMembres->timer();

?><script>
    jQuery('pre[data-type="json"]').beautifyJSON();
</script>