<?php

/*
 * (DATA-) EVENT STREAM - CLIENT
 */


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
if($_LIVEQA_USER !== false){
    $event_data["data"][$chunk]["username"]=$_LIVEQA_USER["name"];
    $event_data["data"][$chunk]["session"]=$_LIVEQA_USER["session"];
    $event_data["data"][$chunk]["level"]=$_LIVEQA_USER["level"];
    $event_data["data"][$chunk]["os"]=$_LIVEQA_USER["os"];
    $event_data["data"][$chunk]["mod"]=$_LIVEQA_USER["mod"];
}else{
    $event_data["data"][$chunk]["unset"]=1;
}

send_event("sys", $event_data);

$listen_socket=socket_create(AF_INET, SOCK_STREAM, 0);

if($_LIVEQA_USER === false){
    $bind_port_user=-1;
}else{
    $bind_port_user=$_LIVEQA_USER["id"];
}

$port=set_bind_port($bind_port_user);

if(socket_bind($listen_socket, "127.0.0.1", $port)
   and socket_listen($listen_socket, 10)) {
    
    while (!connection_aborted()){
        $communication_socket=socket_accept($listen_socket);
        
        $message=trim(socket_read($communication_socket, 5000,  PHP_NORMAL_READ));
        socket_write($communication_socket, "OK\n", 16);
        
        $message_parsed=json_decode($message, true);
        
        send_event($message_parsed["event"], array("data" =>  [ $message_parsed["data"] ]));
    }

}

socket_close($listen_socket);
open_bind_port($port);

error_log("Closing Connection...");
mysqli_close($mysql_connection);

?>
    
