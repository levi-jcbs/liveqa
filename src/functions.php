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
        send_error("Connection to mysql failed!");
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
        send_error(mysqli_error($mysql_connection));
    }
    
    return $out;
}

function getrows($query_out){
    global $mysql_connection;
    
    if($query_out === false){
        send_error(mysqli_error($mysql_connection));
        return array();
    }else{
        return mysqli_fetch_row($query_out);
    }
}

function handle_user(){
    global $_LIVEQA_CONFIG;

    /* 1. Valide Session ID auslesen, falls nicht möglich, die invalide Session ID aus den Cookies löschen.
     * 2. Überprüfen, ob Session ID einem existierenden User zugeordnet werden kann. (Überspringen, falls keine Session ID ausgelesen werden konnte)
     * 3. Wenn Session ID nicht zugeordnet werden kann, neue generieren und User erstellen. 
     */
    
    # Retrieving Session ID. If failed, clear Session Cookies.
    if(session_start(["sid_length" => 32])){
        $_LIVEQA_USER["session"]=session_id();
        session_write_close();
    }else{
        $setcookie=setcookie(session_name(), null, -1, '/');
    }
    
    $user_exists=false;
    $i=0;
    while (!$user_exists and $i < 2){
        # Dont look for "empty" session, when Session ID not retreived. 
        if($_LIVEQA_USER["session"] != ""){
            $out = query("SELECT id, name, os, level FROM user WHERE session='".escape($_LIVEQA_USER["session"])."';");
            while ($row = getrows($out)) {
                $user_exists=true;
                $_LIVEQA_USER["id"]=$row[0];
                $_LIVEQA_USER["name"]=$row[1];
                $_LIVEQA_USER["os"]=$row[2];
                $_LIVEQA_USER["level"]=$row[3];
            }
        }
        
        if(!$user_exists){
            # Generate new Session ID to not accept custom session IDs from users.
            if(session_start(["sid_length" => 32])){
                session_regenerate_id(true);
                $_LIVEQA_USER["session"]=session_id();
                session_write_close();
                
                query("INSERT INTO user SET name='Anonymous', os='Linux', level='1', session='".escape($_LIVEQA_USER["session"])."';");
            }else{
                send_error("Session Start failed.");
                break;
            }
        }
    
        $i++;
    }

    if(!$user_exists){
        send_error("Failed creating user. Aborting.");
        exit();
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

function escape($string){
    global $mysql_connection;
    return mysqli_real_escape_string($mysql_connection, $string);
}

?>
