<?php
    
    $word = utf8_decode( ( isset( $_GET["search"] ) ? mb_strtolower( $_GET["search"], 'UTF-8' ) : "" ) );

    $query = array(
        'origen'  => 'RAE'  ,
        'type'    => '3'    ,
        'val_aux' => ''     ,        
        'key'     => $word
    );          
   
    $endpoint = 'http://lema.rae.es/drae/srv/';
    $queryURL = $endpoint . "search?" . http_build_query( $query );

    $fields = array(
        'TS014dfc77_id' => urlencode( "3" ),
        'TS014dfc77_cr' => '1a285e2c3a9cd4734a6c9e597c92c6f5:jihl:c55Mjc2J:1073656524',
        'TS014dfc77_76' => urlencode( "0" ),
        'TS014dfc77_md' => urlencode( "1" ),
        'TS014dfc77_rf' => urlencode( "0" ),
        'TS014dfc77_ct' => urlencode( "0" ),
        'TS014dfc77_pd' => urlencode( "0" )
    );

    $fields_string = http_build_query( $fields );

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt( $ch, CURLOPT_URL           , $queryURL       );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1               );
    curl_setopt( $ch, CURLOPT_POST          , count( $query ) );
    curl_setopt( $ch, CURLOPT_POSTFIELDS    , $fields_string  );


    //execute post
    $result = curl_exec( $ch );

    //close connection
    curl_close( $ch );

    try
    {
        $doc = new DOMDocument();
        @$doc->loadHTML( '<?xml encoding="utf-8" ?>' . $result );
        $doc->saveHTML();

        $xpath  = new DOMXpath($doc);
        $lemas  = $xpath->query( "/html/body/div" );
        $entity = array(); 
        $defs   = array();

        if( $lemas->length > 0 )
        {
            for( $index = 1; $index <= $lemas->length; $index++ )
            {
                $defNodes = $xpath->query( "/html/body/div[{$index}]/p[position()>3]" );
                
                $definiciones = call_user_func( function() use( $defNodes ) 
                { 
                    $i = 0;
                    $ex = false;
                    foreach( $defNodes as $def ) 
                    {
                        $definicion = trim( preg_replace("/(\d+.)/", "", $def->textContent ) );
 
                        if( $def->getAttribute( "class" ) == "p" ) 
                        {
                            $defs[] = array( trim( $definicion ) => array() );
                            $carry  = $i;
                            $carry2 = trim( $definicion );
                            $ex     = true;
                            //var_dump($i." - ".$carry." - ".$carry2."<br>");
                        }
                        elseif( $def->getAttribute( "class" ) == "q" and $ex )
                        {
//                            var_dump($i." - ".$carry." - ".$carry2."<br>", $defs );
                            $defs[ $carry ][ $carry2 ][] = trim( $definicion );
                        }                                                        
                        else
                        {
                            $defs[] = trim( $definicion );
                        }
                        
                        if( ( $def->getAttribute( "class" ) == "q" and !$ex ) OR $def->getAttribute( "class" ) == "p" OR $def->getAttribute( "class" ) == "m"  ) 
                        {
                            $i++;
                        }
                    }

                    return $defs; 
                 });

                 $entity[] = array(
                    "etimología"    => ( $xpath->query( "/html/body/div[{$index}]/p[2]" )->length > 0 ? trim( $xpath->query( "/html/body/div[{$index}]/p" )->item( 1 )->textContent )        : NULL ) ,
                    "id"            => ( $xpath->query( "/html/body/div[{$index}]/a" )   ->length > 0 ? $xpath->query( "/html/body/div[{$index}]/a" )->item( 0 )      ->getAttribute("name") : NULL ) ,
                    "lema"          => $xpath  ->query("/html/body/div[{$index}]/p")     ->item( 0 )->textContent                                                                                     ,
                    "definiciones"  => $definiciones
                ); 
            }                
        }
        else
        {
            $altNodes     = $xpath->query( "/html/body/ul/li/a" );
            if( $altNodes->length > 0 )
            {
                $alternativas = call_user_func( function() use( $altNodes, $endpoint ) 
                { 
                    foreach( $altNodes as $alt ) 
                    {
                        $definicion = trim( $alt->textContent );
                        $defs[]     = array( $definicion => $endpoint . $alt->getAttribute( "href" ) );

                    }
                     
                    return $defs; 
                 });                

                $mensaje = ( isset( $xpath->query( "/html/body/p/span" )->item(0)->textContent ) ? $xpath->query( "/html/body/p/span" )->item(0)->textContent : false ); 

                $entity  = array(
                    "error"        => TRUE,
                    "mensaje"      => $mensaje,
                    "alternativas" => $alternativas
                );
            }
            else
            {
                $mensaje = $xpath->query( "/html/body/p/font" );

                if( $mensaje->length > 0 )
                {
                    $entity = array(
                        "error"   => TRUE,
                        "mensaje" => $mensaje->item(0)->textContent
                    );
                }
                else
                {
                    $entity = array(
                        "error"   => TRUE,
                        "mensaje" => "Error desconocido"
                    );
                }
            }
        }
        
        header( "Content-Type: application/json" );
        print( json_encode( $entity ) );
    }
    catch( Exception $e )
    {

    }