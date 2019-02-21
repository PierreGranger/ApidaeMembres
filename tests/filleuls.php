<?php

	include(realpath(dirname(__FILE__)).'/../vendor/autoload.php') ;
	include(realpath(dirname(__FILE__)).'/../config.inc.php') ;

    ini_set('display_errors',1) ;
    error_reporting(E_ALL) ;

	if (php_sapi_name() !== "cli") echo '<pre>' ;

    $configApidaeMembres['type_prod'] = 'preprod' ;
    $configApidaeMembres['debug'] = true ;
    $ad = new \PierreGranger\ApidaeMembres($configApidaeMembres) ;
    
    $idParrain = ( isset($utilisateurApidae) ) ? $utilisateurApidae['membre']['id'] : 1147 ; // Allier Tourisme

    try {
        $filleuls = $ad->getFilleuls($idParrain) ;
    }
    catch ( Exception $e ) {
        print_r($e) ;
        die() ;
    }


    $lignes = Array() ;
    $mailTo = Array() ;
    $mailJet = Array() ;

    foreach ( $filleuls as $f )
    {
        if ( isset($f['utilisateurs']) )
        {
            foreach ( $f['utilisateurs'] as $u )
            {
                $ligne = Array() ;
                $ligne['StructureId'] = $f['id'] ;
                $ligne['StructureNom'] = $f['nom'] ;
                $ligne['StructureTypeId'] = $f['type']['id'] ;
                $ligne['StructureTypeNom'] = $f['type']['nom'] ;
                foreach ( $u as $k => $v ) $ligne[$k] = $v ;
                $lignes[] = $ligne ;
                $mailTo[] = $u['prenom'].' '.$u['nom'].' <'.$u['eMail'].'>' ;
                $mailJet[] = $u['prenom']."\t".$u['nom']."\t".$u['eMail'] ;
            }
        }
    }

    echo '<h1>Mes filleuls (#'.$idParrain.' / '.@$utilisateurApidae['membre']['nom'].') ('.sizeof($filleuls).' membres)</h1>' ;

    
    echo '<h2>Pour c/c par mail</h2>' ;
    echo '<textarea style="color:white;background:black;width:100%;height:100px;">' ;
        echo htmlentities(implode(';',$mailTo)) ;
    echo '</textarea>' ;

    echo '<h2>Pour c/c sur mailjet</h2>' ;
    echo '<textarea style="color:white;background:black;width:100%;height:100px;">' ;
        echo htmlentities(implode("\n",$mailJet)) ;
    echo '</textarea>' ;

    echo '<h2>Pour c/c sur excel</h2>' ;
    echo '<table style="font-family:monospace;" border="1" cellspacing="0" cellpadding="4">' ;
        $head = false ;
        foreach ( $lignes as $l )
        {
            if ( ! $head )
            {
                $head = true ;
                echo '<thead>' ;
                    echo '<tr>' ;
                    foreach ( $l as $k => $c )
                    {
                        echo '<th>'.$k.'</th>' ;
                    }
                    echo '</tr>' ;
                echo '</thead>' ;
                echo '<tbody>' ;
            }
            echo '<tr>' ;
            foreach ( $l as $c )
            {
                echo '<td>'.$c.'</td>' ;
            }
            echo '</tr>' ;
        }
        echo '</tbody>' ;
    echo '</table>' ;
