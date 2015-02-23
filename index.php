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
else {
  echo "Error: unknown endpoint \"$endpoint\".";
}


?>
