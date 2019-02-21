<?php

	include(realpath(dirname(__FILE__)).'/../vendor/autoload.php') ;
	include(realpath(dirname(__FILE__)).'/../config.inc.php') ;

    ini_set('display_errors',1) ;
    error_reporting(E_ALL) ;

    $configApidaeMembres['type_prod'] = 'preprod' ;
    $configApidaeMembres['debug'] = true ;
    $ad = new \PierreGranger\ApidaeMembres($configApidaeMembres) ;
    
    $id_membre = 1147 ;

    echo '<script src="//code.jquery.com/jquery-1.12.2.min.js"></script>'.PHP_EOL ;
    echo '<script src="./jquery.beautify-json.js"></script>'.PHP_EOL ;
    echo '<link rel="stylesheet" type="text/css" href="./beautify-json.css">'.PHP_EOL ;
    
    echo '<h1>tests getMembre</h1>' ;

    try {
    
        echo '<h2>getMembreById('.$id_membre.')</h2>'.PHP_EOL ;
        $membreById = $ad->getmembreById($id_membre,Array('PROJETS')) ;
        echo '<pre data-type="json">'.json_encode($membreById).'</pre>'.PHP_EOL ;
    }
    catch ( Exception $e ) {
        print_r($e) ;
        die() ;
    }

?><script>jQuery('pre[data-type="json"]').beautifyJSON();</script>
    