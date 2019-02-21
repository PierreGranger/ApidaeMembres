<?php

	include(realpath(dirname(__FILE__)).'/../vendor/autoload.php') ;
	include(realpath(dirname(__FILE__)).'/../config.inc.php') ;

    ini_set('display_errors',1) ;
    error_reporting(E_ALL) ;

	if (php_sapi_name() !== "cli") echo '<pre>' ;

    $configApidaeMembres['type_prod'] = 'preprod' ;
    $ad = new \PierreGranger\ApidaeMembres($configApidaeMembres) ;
    
    try {

        $responseFields = Array("PROJETS") ;
        $query = Array( 'communeCode'=>"03400" ) ;
        $membresCommune = $ad->getMembres($query,$responseFields) ; 
        
        echo '<h2>Recherche des abonnés...</h2>' ;
        echo '<pre>'.json_encode($query).'</pre>' ;
        $membresAbonnes = Array() ;
        foreach ( $membresCommune as $mc )
        {
            echo '<h3>'.$mc['nom'].'</h3>' ;
            if ( isset($mc['projets']) )
            {
                foreach ( $mc['projets'] as $p )
                {
                    echo '<h4>'.$p['id'].'/'.$p['nom'].'</h4>' ;
                    if ( $p['id'] == 2792 ) // ApidaeEvent (multi-membres)
                    {
                        $membresAbonnes[] = $mc ;
                    }
                }
            }
        }

        echo '<hr />' ;

        echo '<h2>Membres abonnés ('.sizeof($membresAbonnes).')</h2>' ;

        // On a les abonnés : s'il y en a plusieurs sur la commune, on doit chercher le plus petit.
        foreach ( $membresAbonnes as $ma )
        {
            echo '<h3>'.$ma['nom'].'</h3>' ;
            //echo json_encode($ma,JSON_PRETTY_PRINT) ;
        }

        //echo json_encode($membresCommune,JSON_PRETTY_PRINT) ;
        
    }
    catch ( Exception $e ) {
        print_r($e) ;
    }