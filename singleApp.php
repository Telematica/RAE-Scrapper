<?php
    
    $word = utf8_decode( ( isset( $_GET["search"] ) ? $_GET["search"] : "" ) );

    $query = array(
        'origen'  => 'RAE'  ,
        'type'    => '3'    ,
        'val_aux' => ''     ,        
        'val'     => $word
    );          

    $endpoint= 'http://lema.rae.es/drae/srv/search?' . http_build_query($query);
    
    $fields = array(
        'TS014dfc77_id' => urlencode("3"),
        'TS014dfc77_cr' => '1a285e2c3a9cd4734a6c9e597c92c6f5:jihl:c55Mjc2J:1073656524',
        'TS014dfc77_76' => urlencode("0"),
        'TS014dfc77_md' => urlencode("1"),
        'TS014dfc77_rf' => urlencode("0"),
        'TS014dfc77_ct' => urlencode("0"),
        'TS014dfc77_pd' => urlencode("0")
    );

    $fields_string = http_build_query($fields);

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $endpoint);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_POST, count($query));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);


    //execute post
    $result = curl_exec($ch);

    //close connection
    curl_close($ch);

    try
    {
        $doc = new DOMDocument();
        @$doc->loadHTML( '<?xml encoding="utf-8" ?>' . $result);
        $doc->saveHTML();

        $xpath = new DOMXpath($doc);

        $lemas = $xpath->query("/html/body/div");

        $entity       = array(); 
        $defs = array();

        if( $lemas->length > 0 )
        {
            for( $index = 0; $index < $lemas->length; $index++ )
            {
                $defNodes = $xpath->query("/html/body/div/p[position()>3]");
                $definiciones = function() use( $defNodes ) { foreach( $defNodes as $def ) { $defs[] = trim($def->textContent); } return $defs; };

                $entity[] = array(
                    "etimología"    => ( $xpath->query("/html/body/div/p[2]")->length > 0 ? trim( $xpath->query("/html/body/div/p")->item(1)->textContent )  : NULL ) ,
                    "id"            => ( $xpath->query("/html/body/div/a")->length    > 0 ? $xpath->query("/html/body/div/a")->item(0)->getAttribute("name") : NULL ) ,
                    "lema"          => $xpath->query("/html/body/div/p")->item(0)->textContent                                                                        ,
                    "definiciones"  => $definiciones()
                ); 
            }                
        }
        else
        {

        }
        header("Content-Type: application/json");
        print(json_encode($entity));
    }
    catch(Exception $e)
    {

    }

  