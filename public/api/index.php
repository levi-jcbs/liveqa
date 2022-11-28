<?php

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

    # MOD REQUESTS
    if($_LIVEQA_USER["mod"]){
        switch($full_request){

            # SYS: NEW - PROJECT
        case "sys:new-project":
            if(!($_REQ["content"] != "")){ content_or_id_missing(); break; }
            $out = query("SELECT id FROM projects WHERE active=1;");
            while ($row = getrows($out)){
                $remove_active_project_id=$row[0];
                query("UPDATE projects SET active=0 WHERE id=$remove_active_project_id;");

                deploy_chunk(array(
                    "event" => "sys",
                    "data" => [
                        "type" => "project",
                        "id" => $remove_active_project_id,
                        "active" => 0
                    ]
                ), -1);
            }
            
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
                    "name" => html_escape($_REQ["content"]),
                    "active" => 1
                ]
            ), -1);
            $_RETURN["status"]="success";
            break;
            
        }
    }
    
}else{
    $_RETURN["status"]="error";
    $_RETURN["error"]="User Init failed.";
}

echo json_encode($_RETURN);

?>
