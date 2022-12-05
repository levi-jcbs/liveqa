<?php

/*
 * API - BACKEND
 */

include("../../src/functions.php");

$_LIVEQA_CONFIG=import_config();
open_mysql_connection();
$_LIVEQA_USER=handle_user();
$_LIVEQA_PROJECT=handle_project();

$_RETURN=array();

$_REQ["group"]=$_GET["group"];
$_REQ["action"]=$_GET["action"];
$_REQ["type"]=$_GET["type"];
$_REQ["property"]=$_GET["property"];

$_REQ["id"]=$_GET["id"];
$_REQ["content"]=$_GET["content"];

$full_request=$_REQ["group"].":"
             .$_REQ["action"]."-"
             .$_REQ["type"];

if($_REQ["property"] != ""){
    $full_request.="(".$_REQ["property"].")";
}

if($_LIVEQA_USER !== false){

    if($_LIVEQA_USER["mod"]){

        # MOD REQUESTS
        switch($full_request){
            
            # SYS: SET - PROJECT (ACTIVE)
        case "sys:set-project(active)":
            if(!($_REQ["id"] != "")){ content_or_id_missing(); break; }
            
            $out = query("SELECT id FROM projects WHERE id='".mysql_escape($_REQ["id"])."';");
            while ($row = getrows($out)){
                $project_id=$row[0];
            }
            
            if(is_numeric($project_id)){
                unactivate_all_projects();

                query("UPDATE projects SET active=1 WHERE id='$project_id';");

                deploy_chunk(array(
                    "event" => "sys",
                    "data" => [
                        "type" => "project",
                        "id" => $project_id,
                        "active" => 1
                    ]
                ), -2);
                
                $_RETURN["status"]="success";
            }else{
                $_RETURN["status"]="error";
                $_RETURN["error"]="Project to activate not found";
            }
            
            break;
            
            # SYS: NEW - PROJECT
        case "sys:new-project":
            if(!($_REQ["content"] != "")){ content_or_id_missing(); break; }
            
            unactivate_all_projects();
            query("INSERT INTO projects SET name='".mysql_escape($_REQ["content"])."', active=1;");
            
            $out = query("SELECT LAST_INSERT_ID();");
            while ($row = getrows($out)){
                $project_id=$row[0];
            }
            
            deploy_chunk(array(
                "event" => "sys",
                "data" => [
                    "type" => "project",
                    "id" => $project_id,
                    "name" => $_REQ["content"],
                    "active" => 1
                ]
            ), -2);

            $_RETURN["status"]="success";
            break;
            
        }
    }

    # USER REQUESTS
    switch($full_request){
        
        # SYS: SET - USER (SESSION)
    case "sys:set-user(session)":
        if(!($_REQ["content"] != "")){ content_or_id_missing(); break; }
        
        $out = query("SELECT session FROM user WHERE session='".mysql_escape($_REQ["content"])."';");
        while ($row = getrows($out)){
            $new_session_id=$row[0];
        }
        
        if($new_session_id != ""){
            session_id($new_session_id);
            session_start();
            
            $_RETURN["status"]="success";
        }else{
            $_RETURN["status"]="error";
            $_RETURN["error"]="Session ID not found";
        }
        
        break;
            
        # SYS: SET - USER (NAME)
    case "sys:set-user(name)":
        if(!($_REQ["content"] != "")){ content_or_id_missing(); break; }

        query("UPDATE user SET name='".mysql_escape($_REQ["content"])."' WHERE id='".$_LIVEQA_USER["id"]."';");
            
        deploy_chunk(array(
            "event" => "sys",
            "data" => [
                "type" => "user",
                "name" => $_REQ["content"]
            ]
        ), -3);
            
        $_RETURN["status"]="success";
        break;

        # SYS: SET - USER (LEVEL)
    case "sys:set-user(level)":
        if(!($_REQ["content"] != "")){ content_or_id_missing(); break; }

        $new_level=$_REQ["content"];
        if(is_numeric($new_level) and $new_level >= 0 and $new_level <= 3){
            query("UPDATE user SET level='$new_level' WHERE id='".$_LIVEQA_USER["id"]."';");

            deploy_chunk(array(
                "event" => "sys",
                "data" => [
                    "type" => "user",
                    "level" => $new_level
                ]
            ), -3);

            $_RETURN["status"]="success";
        }else{
            $_RETURN["status"]="error";
            $_RETURN["error"]="Invalid Level";
        }
            
        break;

        # SYS: SET - USER (OS)
    case "sys:set-user(os)":
        if(!($_REQ["content"] != "")){ content_or_id_missing(); break; }
        
        $new_os=$_REQ["content"];
        query("UPDATE user SET os='".mysql_escape($new_os)."' WHERE id='".$_LIVEQA_USER["id"]."';");
        
        deploy_chunk(array(
            "event" => "sys",
            "data" => [
                "type" => "user",
                "os" => $new_os
            ]
        ), -3);
        
        $_RETURN["status"]="success";
        break;

        # SYS: NEW - FRAGE
    case "sys:new-frage":
        if(!($_REQ["content"] != "")){ content_or_id_missing(); break; }
        
        $frage_inhalt=$_REQ["content"];

        query("INSERT INTO fragen SET user='".$_LIVEQA_USER["id"]."', project='".$_LIVEQA_PROJECT["id"]."', time='".time()."', inhalt='".mysql_escape($frage_inhalt)."';");
        
        $out = query("SELECT LAST_INSERT_ID();");
        while ($row = getrows($out)){
            $frage_id=$row[0];
        }
        
        deploy_chunk(array(
            "event" => "content",
            "data" => [
                "type" => "frage",
                "id" => $frage_id,
                "username" => $_LIVEQA_USER["name"],
                "level" => $_LIVEQA_USER["level"],
                "os" => $_LIVEQA_USER["os"],
                "forum" => 0,
                "interessant" => 0,
                "user_interessant" => 0,
                "inhalt" => $frage_inhalt,
                "status" => 0
            ]
        ), -1);
        
        $_RETURN["status"]="success";
        break;

    }
    
}else{
    $_RETURN["status"]="error";
    $_RETURN["error"]="User Init failed.";
}

echo json_encode($_RETURN);

?>
