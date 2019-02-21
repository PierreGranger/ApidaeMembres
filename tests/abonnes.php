<?php

    require(realpath(dirname(__FILE__)).'/../vendor/autoload.php') ;
    require(realpath(dirname(__FILE__)).'/../config.inc.php') ;
    require(realpath(dirname(__FILE__)).'/runtimes.inc.php') ;

    $ApidaeMembres = new \PierreGranger\ApidaeMembres($configApidaeMembres) ;

    $projet_recherche = 2792 ; // ApidaeEvent (multi-membres)

    ruStart('getMembres') ;
    try {

        $responseFields = Array("PROJETS") ;
        $query = Array( 'communeCode'=>"03100" ) ;
        $membresCommune = $ApidaeMembres->getMembres($query,$responseFields) ; 
        
        echo '<h2>Recherche des membres concernés par la commune '.implode(',',$query).'...</h2>' ;
        echo '<pre>'.json_encode($query).'</pre>' ;
        $membresAbonnes = Array() ;
        foreach ( $membresCommune as $mc )
        {
            echo '<h3>#'.$mc['id'].' '.$mc['nom'].'</h3>' ;
            if ( isset($mc['projets']) )
            {
                echo '<ul>' ;
                foreach ( $mc['projets'] as $p )
                {
                    echo '<li>' ;
                        if ( $p['id'] == $projet_recherche ) echo '<strong>' ;
                        echo '#'.$p['id'].' : '.$p['nom'] ;
                        if ( $p['id'] == $projet_recherche ) { echo '</strong>' ; $membresAbonnes[] = $mc ; }
                    echo '</li>' ;
                }
                echo '</ul>' ;
            }
        }

    }
    catch ( Exception $e ) {
        print_r($e) ;
        die() ;
    }
    ruShow('getMembres') ;

    echo '<hr />' ;

    echo '<h2>Membres abonnés au projet '.$projet_recherche.' sur la recherche '.json_encode($query).' ('.sizeof($membresAbonnes).')</h2>' ;

    // On a les abonnés : s'il y en a plusieurs sur la commune, on doit chercher le plus petit.
    foreach ( $membresAbonnes as $ma )
    {
        if ( in_array($ma['id'],\PierreGranger\ApidaeMembres::$idCRT) ) continue ; // On ignore volontairement Apidae Tourisme et Aura Tourisme
        echo '<h3>'.$ma['nom'].'</h3>' ;
        //echo json_encode($ma,JSON_PRETTY_PRINT) ;
    }

    //echo json_encode($membresCommune,JSON_PRETTY_PRINT) ;
    