<?php

	include(realpath(dirname(__FILE__)).'/../vendor/autoload.php') ;
	include(realpath(dirname(__FILE__)).'/../config.inc.php') ;

    ini_set('display_errors',1) ;
    error_reporting(E_ALL) ;

    $_config['type_prod'] = 'preprod' ;
    $_config['debug'] = true ;
    $ad = new \PierreGranger\ApidaeDroits($_config) ;
    
    $id_user = 14015 ;
    $mail_user = 'p.granger@allier-tourisme.net' ;
    $id_membre = 1147 ;

    echo '<script src="//code.jquery.com/jquery-1.12.2.min.js"></script>'.PHP_EOL ;
    echo '<script src="./jquery.beautify-json.js"></script>'.PHP_EOL ;
    echo '<link rel="stylesheet" type="text/css" href="./beautify-json.css">'.PHP_EOL ;


    echo '<h1>tests getUser</h1>' ;

    try {
        $userById = $ad->getUserById($id_user) ;
        $userByMail = $ad->getUserByMail($mail_user) ;
        $usersByMember = $ad->getUsersByMember($id_membre) ;
    }
    catch ( Exception $e ) {
        print_r($e) ;
        die() ;
    }

    echo '<h2>getUserById('.$id_user.')</h2>'.PHP_EOL ;
    echo '<pre data-type="json">'.json_encode($userById).'</pre>'.PHP_EOL ;

    echo '<h2>getUserByMail('.$mail_user.')</h2>'.PHP_EOL ;
    echo '<pre data-type="json">'.json_encode($userByMail).'</pre>'.PHP_EOL ;

    echo '<h2>getUsersByMember('.$id_membre.')</h2>'.PHP_EOL ;
    echo '<pre data-type="json">'.json_encode($usersByMember).'</pre>'.PHP_EOL ;

?><script>jQuery('pre[data-type="json"]').beautifyJSON();</script>
    