<?php

//error_reporting(E_ALL ^ E_DEPRECATED); // supress deprecation warnings
$debug       = false;
$api         = "/api/index.php/";

function logc( $data) {
  global $debug;
  if( !$debug) return;
  
  if( is_array( $data)) {
    $output = "<script>console.log( '" . implode( ',', $data) . "' );</script>";
  } else {
    $output = "<script>console.log( '" . addslashes( $data) . "' );</script>";
  }
  echo $output;
}

function call_api($method, $url, $data = false)
{
    $curl = curl_init();
    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}

function startsWith( $haystack, $needle)
{
  $length = strlen($needle);
  return (substr($haystack, 0, $length) === $needle);
}

function endsWith( $haystack, $needle)
{
  $length = strlen($needle);
  if ($length == 0) {
    return true;
  }
  return (substr($haystack, -$length) === $needle);
}

logc( "Hello from Test API ($api)");

$method = $_SERVER['REQUEST_METHOD'];
logc( "method: $method");

$path = $_SERVER['REQUEST_URI'];
logc( "uri: $path");

/*
foreach($_SERVER as $key => $value) {
  echo "_server $key: $value <br/>";
}
*/

$endpoint = substr( $path, strlen( $api));
logc( "endpoint: $endpoint");

/*
foreach($_REQUEST as $key => $value) {
  logc( "query: $key: $value");
}
*/


if (startsWith( $endpoint, "double")) {
  
  if ($method== "GET") {
     logc( "GET 'double' endpoint");      
     if( isset( $_REQUEST['num'])) {
       $num = $_REQUEST['num'];    
       $value = $num * 2;
       $result = new stdClass();
       $result->value = $value;
       $result_json = json_encode( $result, 128); //JSON_PRETTY_PRINT);
       echo $result_json;
     }
     else {
       die( "ERROR: Missing parameter 'num' for endpoint 'double'");    
    }   
  }
}

// other endpoint

else if (startsWith( $endpoint, "mirror")) {
  
  if ($method== "GET") {
     if( isset( $_REQUEST['text'])) {
       $text = $_REQUEST['text'];    
       $value = strrev( $text);
       $result = new stdClass();
       $result->value = $value;
       $result_json = json_encode( $result, 128); //JSON_PRETTY_PRINT);
       echo $result_json;
     }
     else {
       die( "ERROR: Missing parameter 'text' for endpoint 'mirror'");    
    }   
  }
}

else if (startsWith( $endpoint, "geocode")) {
  
  if ($method== "GET") {
     if( isset( $_REQUEST['text'])) {
       $text = $_REQUEST['text']; 
         
       $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $text;
       $response_raw = call_api( "GET", $url);
       $response_json = json_decode( $response_raw);
       //$geometry = $value->geometry;
       $results = $response_json->results;
       $top_result = $results[ 0];
       $lat = $top_result->geometry->location->lat;
       $long = $top_result->geometry->location->lng;
       $result = new stdClass();
       $result->lat = $lat;
       $result->long = $long;
         
       
       $result_json = json_encode( $result, 128); //JSON_PRETTY_PRINT);
       echo $result_json;
     }
     else {
       die( "ERROR: Missing parameter 'text' for endpoint 'mirror'");    
    }   
  }
}

else if (startsWith( $endpoint, "json")) {
  
  if ($method== "GET") {
     if( isset( $_REQUEST['text'])) {
       $text = $_REQUEST['text']; 
         
       $response_json = json_decode( $text);
       $result = new stdClass();
       $result->json = $response_json;       
       $result_json = json_encode( $result, 128); //JSON_PRETTY_PRINT);
       echo $result_json;
     }
     else {
       die( "ERROR: Missing parameter 'text' for endpoint 'mirror'");    
    }   
  }
}


else {
  echo "Error: unknown endpoint \"$endpoint\".";
}


?>
