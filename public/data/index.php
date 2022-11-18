<?php

include("../../src/functions.php");

set_eventstream();
$_LIVEQA_CONFIG=import_config();
open_mysql_connection();
$_LIVEQA_USER=handle_user();
$_LIVEQA_PROJECT=handle_project();

#                      #
# SENDING INITIAL DATA #
#                      #

# SYS #
$event_data=array();
$chunk=0;
$event_data["data"][$chunk]["type"]="text";
$event_data["data"][$chunk]["host"]=$_LIVEQA_CONFIG["text"]["host"];
$event_data["data"][$chunk]["headline"]=$_LIVEQA_CONFIG["text"]["headline"];

foreach($_LIVEQA_CONFIG["css"] AS $cssevent_data){
    $chunk++;
    $event_data["data"][$chunk]["type"]="css";
    $event_data["data"][$chunk]["key"]=$cssevent_data[0];
    $event_data["data"][$chunk]["value"]=$cssevent_data[1];
}

$out = query("SELECT id, name, active FROM projects;");
while ($row = getrows($out)) {
    $chunk++;
    $event_data["data"][$chunk]["type"]="project";
    $event_data["data"][$chunk]["id"]=$row[0];
    $event_data["data"][$chunk]["name"]=$row[1];
    $event_data["data"][$chunk]["active"]=$row[2];
}

$chunk++;
$event_data["data"][$chunk]["type"]="user";
$event_data["data"][$chunk]["username"]=$_LIVEQA_USER["name"];
$event_data["data"][$chunk]["session"]=$_LIVEQA_USER["session"];
$event_data["data"][$chunk]["level"]=$_LIVEQA_USER["level"];
$event_data["data"][$chunk]["os"]=$_LIVEQA_USER["os"];
$event_data["data"][$chunk]["mod"]=$_LIVEQA_USER["mod"];


send_event("sys", $event_data);

while (!connection_aborted()){
    sleep(10);
}

?>
    
