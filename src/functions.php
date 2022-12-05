<?php

session_set_cookie_params(["SameSite" => "Strict"]);
session_set_cookie_params(["Secure" => "true"]);

mysqli_report(MYSQLI_REPORT_OFF);

function open_mysql_connection(){
    global $mysql_connection;

    $host="localhost";
    $user="liveqa";
    $password="liveqa";
    $database="liveqa";
    
    $mysql_connection = mysqli_connect($host, $user, $password);

    if($mysql_connection !== false){
        $result=mysqli_select_db($mysql_connection, $database);
    }else{
        $result=false;
    }

    if(!$result){
        error_log("Connection to mysql failed!");
        return false;
    }else{
        return true;
    }
}

function send_error($description){
    echo "event: liveqa_error\n";
    echo "data: Error message: ".$description."\n\n";
    flush();
}

function query($query){
    global $mysql_connection;

    $out = mysqli_query($mysql_connection, $query);

    if($out === false){
        error_log(mysqli_error($mysql_connection));
    }
    
    return $out;
}

function getrows($query_out){
    global $mysql_connection;
    
    if($query_out === false){
        error_log(mysqli_error($mysql_connection));
        return array();
    }else{
        return mysqli_fetch_row($query_out);
    }
}

function handle_user(){
    global $_LIVEQA_CONFIG;
    global $_GET;
    
    if($_GET["cookies_allowed"] == "0"){
        error_log("Cookies disallowed by browser");
        return false;
    }
    
    /* 1. Valide Session ID auslesen, falls nicht möglich, die invalide Session ID aus den Cookies löschen.
     * 2. Überprüfen, ob Session ID einem existierenden User zugeordnet werden kann.
     * 3. Wenn Session ID nicht zugeordnet werden kann, User erstellen.
     */

    # 1
    if(session_start(["sid_length" => 32])){
        $_LIVEQA_USER["session"]=session_id();
        session_write_close();
    }else{
        setcookie(session_name(), null, -1, '/');
        error_log("Invalid Session Cookie detected - deleting");
        return false;
    }

    $user_exists=false;
    $i=0;
    while (!$user_exists and $i < 2){
        # 2
        $out = query("SELECT id, name, os, level FROM user WHERE session='".mysql_escape($_LIVEQA_USER["session"])."';");
        while ($row = getrows($out)) {
            $user_exists=true;
            $_LIVEQA_USER["id"]=$row[0];
            $_LIVEQA_USER["name"]=$row[1];
            $_LIVEQA_USER["os"]=$row[2];
            $_LIVEQA_USER["level"]=$row[3];
        }

        # 3
        if(!$user_exists){
            query("INSERT INTO user SET name='Anonymous', os='Linux', level='1', session='".mysql_escape($_LIVEQA_USER["session"])."';");
        }
    
        $i++;
    }
    
    if(!$user_exists){
        error_log("Failed creating user. Aborting.");
        return false;
    }

    if(in_array($_LIVEQA_USER["session"], $_LIVEQA_CONFIG["mods"])){
        $_LIVEQA_USER["mod"]=true;
    }else{
        $_LIVEQA_USER["mod"]=false;
    }
    
    return $_LIVEQA_USER;
}

function handle_project(){
    $project_exists=false;
    $i=0;
    while (!$project_exists and $i < 2){
        $project_exists=false;
        $out = query("SELECT id, name FROM projects WHERE active='1';");
        while ($row = getrows($out)) {
            $project_exists=true;
            $_LIVEQA_PROJECT["id"]=$row[0];
            $_LIVEQA_PROJECT["name"]=$row[1];
        }
        
        if(!$project_exists){
            query("INSERT INTO projects SET name='First project', active=1;");
        }
        
        $i++;
    }

    return $_LIVEQA_PROJECT;
}

function import_config(){
    $config=file_get_contents("../../config/liveqa.json");
    $config=json_decode($config, true);

    return $config;
}

function set_eventstream(){
    set_time_limit(0);
    ob_end_flush();
    ignore_user_abort(true);
    
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');

    return true;
}

function send_event($event, $data){
    echo "event: $event\n";
    echo "data: ".json_encode($data)."\n\n";
    flush();
}

function mysql_escape($string){
    global $mysql_connection;
    return mysqli_real_escape_string($mysql_connection, $string);
}

function html_escape($string){
    return htmlspecialchars($string);
}

function content_or_id_missing(){
    global $_RETURN;

    $_RETURN["status"]="error";
    $_RETURN["error"]="Content or ID missing.";
}

function set_bind_port($user_id){
    global $mysql_connection;
    
    $port=25000;
    $port_free=false;
    while (!$port_free and $port < 26000){
        $port_free=true;
        $out = query("SELECT id FROM sockets WHERE port=$port;");
        while ($row = getrows($out)) {
            $port_free=false;
        }
        
        if($port_free){
            query("INSERT INTO sockets SET user=$user_id, port=$port;");
            return $port;
        }
        
        $port++;
    }

    return false;
}

function open_bind_port($port){
    query("DELETE FROM sockets WHERE port=$port;");
    return true;
}

function deploy_chunk($event, $user){
    # $user:
    # 0+ Ein spezieller User mit ID
    # -1 Alle User
    # -2 Mods
    # -3 Selbst
    
    global $_LIVEQA_CONFIG;
    global $_LIVEQA_USER;
    
    if(is_numeric($user) and $user > -1){
        $user_specification="WHERE user=$user";
    }elseif($user == -2){
        $user_specification="WHERE 0=1";
        foreach($_LIVEQA_CONFIG["mods"] AS $mod_session_id){
            $out = query("SELECT id FROM user WHERE session='$mod_session_id';");
            while ($row = getrows($out)) {
                $mod_user_id=$row[0];
                $user_specification.=" OR user=$mod_user_id";
            }
        }
    }elseif($user == -3){
        $user_specification="WHERE user=".$_LIVEQA_USER["id"];
    }else{
        $user_specification="";
    }

    $out = query("SELECT id, port FROM sockets $user_specification;");
    while ($row = getrows($out)) {
        $port=$row[1];
        $socket=socket_create(AF_INET, SOCK_STREAM, 0);
        if(socket_connect($socket, "127.0.0.1", $port)){
            $message=json_encode($event);
            socket_write($socket, $message."\n", strlen($message)+2);

            # Wait for answer, to close socket.
            socket_read($socket, 1024, PHP_NORMAL_READ);
        }else{
            open_bind_port($port);
        }
        socket_close($socket);
    }
    
    return true;
}

function unactivate_all_projects(){
    $out = query("SELECT id FROM projects WHERE active=1;");
    while ($row = getrows($out)){
        $unactivate_project_id=$row[0];
        query("UPDATE projects SET active=0 WHERE id=$unactivate_project_id;");
        deploy_chunk(array(
            "event" => "sys",
            "data" => [
                "type" => "project",
                "id" => $unactivate_project_id,
                "active" => 0
            ]
        ), -2);
    }
}

?>
