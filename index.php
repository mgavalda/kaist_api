<?php

error_reporting(E_ALL ^ E_DEPRECATED); // supress deprecation warnings

$debug       = false;

$api         = "/api/index.php/";

//$db_server   = "localhost";
//$db_user     = "root";
//$db_password = "";
//$db_name     = "db_v1";


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

/*
$con = mysql_connect( $db_server, $db_user, $db_password, $db_name);
if (!$con) {
  die('Could not connect: ' . mysql_error());
}

if (!mysql_set_charset( "utf8")) {
    die( 'Error setting character set utf8: ' . mysql_error());
} 

mysql_select_db( $db_name);
*/

/*
  jobs endpoint
  GET params: 
    status: optional; "inbox", "processing", "done"
    limit: optional; default: 10
    
*/
if (startsWith( $endpoint, "jobs")) {
  
  if ($method== "GET") {
    
    logc( "GET 'jobs' endpoint");
    
    $sql = "SELECT * FROM Jobs";    
    $where_already_set = false;
    
    if ( isset($_REQUEST['tenant'])) {
      $tenant = $_REQUEST['tenant'];
      $sql .= " WHERE tenant = '$tenant'";
      $where_already_set = true;
    }

    if ( isset($_REQUEST['language'])) {
      $language = $_REQUEST['language'];
      $sql .= ($where_already_set ? " AND " : " WHERE ") . "language = '$language'";
      $where_already_set = true;
    }
    
    if ( isset($_REQUEST['status'])) {
      $status = $_REQUEST['status'];
      if ( $status == 'processing') {
        $sql .= ($where_already_set ? " AND " : " WHERE ") . "status IN ('classifying','classified','indexing','indexed','searching','searched')";        
      }
      else {
        $sql .= ($where_already_set ? " AND " : " WHERE ") . "status = '$status'";
      }
      $where_already_set = true;
    }

    /*
    if ( isset($_REQUEST['status-list'])) {
      $status_list = $_REQUEST['status-list'];
      $sql .= ($where_already_set ? " AND " : " WHERE ") . "status IN ($status_list)";
      $where_already_set = true;
    }*/

    if ( isset($_REQUEST['since'])) {
      $since = $_REQUEST['since'];
      //$sql .= ($where_already_set ? " AND " : " WHERE") . "inbox_timestamp >= ( NOW() - INTERVAL 24 HOUR)";
      // accepts "10m" (10 minutes), "1h" (1 hour), "7d" (7 days)
      $interval_value = substr( $since, 0, -1);
      $interval_unit = "HOUR";
      if( endsWith( $since, 'm')) {
        $interval_unit = "MINUTE";      
      }
      else if( endsWith( $since, 'd')) {
        $interval_unit = "DAY";      
      }
      $sql .= ($where_already_set ? " AND " : " WHERE") . "inbox_timestamp >= ( NOW() - INTERVAL $interval_value $interval_unit)";
      $where_already_set = true;
    }

    $order_by = isset($_REQUEST['order-by']) ? $_REQUEST['order-by'] : "inbox_timestamp";
    $order_mode = isset($_REQUEST['order-mode']) ? $_REQUEST['order-mode'] : "DESC";
    // &order-by=score&order-mode=ASC
    
    $sql .= " ORDER BY $order_by $order_mode";

    $limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 10;
    $sql .= " LIMIT $limit ";  

    logc( "SQL query: $sql");
       
    $result = mysql_query( $sql);
    if( !$result) {
      die( "ERROR: Could not get job result: " . mysql_error() . "\n");
    }
    logc( "Result" . $result); //qqzz

    $rows = array();
    while( $row = mysql_fetch_assoc( $result)) {
      $rows[] = $row;
    }
    $result_json = json_encode( $rows, 128); //JSON_PRETTY_PRINT);
    //logc( $result_json);
    echo $result_json;
  }
  
  else {
    die( "ERROR: Unsupported method $method for endpoint $endpoint");
  }
    
}

else if (startsWith( $endpoint, "double")) {
  
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

/*
  terms endpoint
  GET params: 
    tenant: required
    lang: required
    
  POST params:  
    tenant: required
    lang: required
    terms_json: required
    returns: term set id
*/
else if (startsWith( $endpoint, "terms")) {
  
  if ($method== "GET") {  
    logc( "GET 'terms' endpoint");
    
    if( isset( $_REQUEST['language'])) {
      $language = $_REQUEST['language'];    
    }
    else {
      die( "ERROR: Missing parameter 'language' for endpoint 'terms'");    
    }
    
    if( isset( $_REQUEST['tenant'])) {
      $tenant = $_REQUEST['tenant'];    
    }
    else {
      die( "ERROR: Missing parameter 'tenant' for endpoint 'terms'");    
    }
    
    $sql = "SELECT * FROM Terms WHERE language = '$language'";

    if( isset( $_REQUEST['id'])) {
      $id = $_REQUEST['id'];    
      $sql .= " AND id = $id";
    }

    $sql .= " ORDER BY id DESC"; // most recent first

    $limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 10;
    $sql .= " LIMIT $limit ";  
    
    logc( "SQL query: $sql");
    $result = mysql_query( $sql);
    if(!$result)
    {
      die( "ERROR: Could not get job result: " . mysql_error() . "\n");
    }

    $rows = array();
    while($row = mysql_fetch_assoc( $result)) {
      $rows[] = $row;
    }
    $result_json = json_encode( $rows, 128); //JSON_PRETTY_PRINT);
    //logc( $result_json);
    echo $result_json;
  }
  
  else if ($method == "POST") {
    logc( "POST 'terms' endpoint");
    
    if( isset( $_REQUEST['tenant'])) {
      $tenant = $_REQUEST['tenant'];    
    }
    else {
      die( "ERROR: Missing parameter 'tenant' for endpoint 'terms'");    
    }
    
    if( isset( $_REQUEST['language'])) {
      $language = $_REQUEST['language'];    
    }
    else {
      die( "ERROR: Missing parameter 'language' for endpoint 'terms'");    
    }
    
    if( isset( $_REQUEST['terms_json'])) {
      $terms_json = $_REQUEST['terms_json'];    
    }
    else {
      die( "ERROR: Missing parameter 'terms_json' for endpoint 'terms'");    
    }
 
    $terms_json_escaped = mysql_real_escape_string( $terms_json);
    
    $sql = 
      "INSERT INTO Terms" .
      " (tenant, language, terms_json)" .
      " VALUES ('$tenant', '$language', '$terms_json_escaped')";
            
    $result = mysql_query( $sql);
    if(!$result)
    {
      die( "ERROR: Could not get a result from a post to 'terms': " . mysql_error() . "\n");
    }
    $termset_id = mysql_insert_id();
    echo $termset_id;
  }
  
  else {
    die( "ERROR: Unsupported method $method for endpoint $endpoint");
  }
    
}
/*
else if (startsWith( $endpoint, "matches")) {
  
  if ($method== "GET") {  
    logc( "GET 'matches' endpoint");

    if( isset( $_REQUEST['lang'])) {
      $lang = $_REQUEST['lang'];    
    }
    else {
      die( "ERROR: Missing parameter 'lang' for endpoint 'matches'");    
    }
    #$terms = $_REQUEST['terms'];
    
    $sql = "SELECT * FROM Results WHERE lang = '$lang'";

    logc( "SQL query: $sql");
    $result = mysqli_query($con, $sql);
    if(!$result) {
      die( "ERROR: Could not get job result: " . mysqli_error() . "\n");
    }

    $rows = array();
    while($row = mysqli_fetch_assoc( $result)) {
      $rows[] = $row;
    }
    $result_json = json_encode($rows, JSON_PRETTY_PRINT);
    //logc( $result_json);
    echo $result_json;
  }
  else if ($method == "POST") {
    logc( "POST 'matches' endpoint");
    $job_id = $_REQUEST['job_id'];
    $terms_id = $_REQUEST['terms_id'];
    $matches = $_REQUEST['matches'];
    $sql = "INSERT XXXX: TODO SELECT Results";

    logc( "SQL query: $sql");
    $result = mysqli_query($con, $sql);
    if(!$result) {
      die( "ERROR: Could not get job result: " . mysqli_error() . "\n");
    }    
  }
  else {
    die( "ERROR: Unsupported method $method for endpoint $endpoint");
  }
}
*/
// other endpoint
else {
  //echo "Error: unknown endpoint \"$endpoint\". Supported enpoints are \"jobs\", \"terms\", and \"matches\".";
  echo "Error: unknown endpoint \"$endpoint\".";
}

//mysqli_close($con);
//mysql_close();




?>
