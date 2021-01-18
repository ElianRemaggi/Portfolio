<?php
function precio($or,$de,$ci){
    /* $origen=$or;
    $destino=$de;
    $ciudad=$ci;  */

      
    $or=strtoupper($or);
    

    $origen=str_replace(' ','%20',$or);
    $destino=str_replace(' ','%20',$de);
    $ciudad=str_replace(' ','%20',$ci);  

 
    //die($origen);
    $curl = curl_init();

     curl_setopt_array($curl, array(
      CURLOPT_URL => "https://maps.googleapis.com/maps/api/geocode/json?address=".$origen."%20".$ciudad."-----------KEY--------------",//key borrada por privacidad
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
    ));
 
    $response = curl_exec($curl);

    curl_close($curl);

    $curl = curl_init();

    
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://maps.googleapis.com/maps/api/geocode/json?address=".$destino."%20".$ciudad."-----------KEY--------------", //key borrada por privacidad
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
    ));

    $response2 = curl_exec($curl);

    curl_close($curl);


    //print_r($response);//origen
    //echo '<br><br><br>';
    //print_r($response2);//destino

    $re=json_decode($response);
    $re2=json_decode($response2);

    $origen_lat=$re->results[0]->geometry->location->lat;
    $origen_lon=$re->results[0]->geometry->location->lng;
    $destino_lat=$re2->results[0]->geometry->location->lat;
    $destino_lon=$re2->results[0]->geometry->location->lng;

    /* echo 'ORIGEN:'.$origen;
    echo '<br>LAT:'.$origen_lat.'<br>LON:';
    echo $origen_lon.'<br>';
    echo 'MAPEADO:'.$re->results[0]->formatted_address;
    echo '<br><br>DESTINO:'.$destino;
    echo '<br>LAT:'.$destino_lat.'<br>LON:';
    echo $destino_lon.'<br>';
    echo 'MAPEADO:'.$re2->results[0]->formatted_address; */
    //ESTIMAR PRECIO

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.glovoapp.com/b2b/orders/estimate",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS =>"{\r\n  \r\n  \"description\": \"A 30cm by 30cm box\",\r\n
          \"addresses\": [\r\n    {\r\n      \"type\": \"PICKUP\",\r\n     \"lat\":".$origen_lat.",\r\n     \"lon\":".$origen_lon.",\r\n      \"label\": \"Calle la X, 29\"\r\n    },\r\n    
          {\r\n      \"type\": \"DELIVERY\",\r\n      \"lat\":".$destino_lat.",\r\n      \"lon\":".$destino_lon.",\r\n      \"label\": \"Calle la X, 30\"\r\n    }\r\n  ]\r\n}",
      CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json",
        "Authorization: Basic MTU4NTM0MTExODkxMzg6YWQyOWM2YmQyMmI4NGFkNGI0ZGFhZjVhODQyMjRlNTk",
        "Content-Type: text/plain"
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    //print_r($response);


     $re=json_decode($response); 

    $precio = 0;
    if(isset($re->total))
      $precio = ($re->total->amount/100);
    
    
    
    return  $precio;

    // Notice: Undefined property: stdClass::$total in /var/www/html/marketplace2/app/code/Compania/Mapsshipping/Model/Carrier/maps.php on line 104
    /* //print_r($re);
    echo '<br><br>TU GLOVO SALE <strong>$ '.($re->total->amount)/100; */
  }