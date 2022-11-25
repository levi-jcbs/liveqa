<?php

include("../../src/functions.php");

$_LIVEQA_CONFIG=import_config();
open_mysql_connection();
$_LIVEQA_USER=handle_user();
$_LIVEQA_PROJECT=handle_project();

$_RETURN=array();

if($_LIVEQA_USER !== false){
    
}else{
    $_RETURN["status"]=false;
}

echo json_encode($_RETURN);
    
?>
