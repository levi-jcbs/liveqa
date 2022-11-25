var dataEventSource = new EventSource("data/");

window.onload = function(){
    dataEventSource.addEventListener("liveqa_error", (event) => {receive_error_event(event);});
    dataEventSource.addEventListener("sys", (event) => {receive_sys_event(event);});
}

function receive_error_event(event){
    alert(event.data);
}

function receive_sys_event(event){
    var data = JSON.parse(event.data).data;
    
    data.forEach(function(chunk){
	if(chunk["type"] == "text"){
	    if(exists(chunk["host"])){
		document.getElementById("data_text_host").innerText = chunk["host"];
	    }
	    if(exists(chunk["headline"])){
		document.getElementById("data_text_headline").innerText = chunk["headline"];
	    }
	}

	if(chunk["type"] == "css"){
	    document.documentElement.style.setProperty(chunk["key"], chunk["value"]);
	}

	if(chunk["type"] == "project"){
	    if(exists(chunk["id"]) && exists(chunk["name"]) && exists(chunk["active"])){
		var selectOption = document.createElement("option");
		selectOption.setAttribute("id", chunk["id"]);
		selectOption.innerText=chunk["id"]+": "+chunk["name"];
		if(chunk["active"] == 1){
		    selectOption.selected=true;
		    selectOption.innerText+=" (aktiv)";
		}

		document.getElementById("set_project").appendChild(selectOption);
	    }
	}

	if(chunk["type"] == "user"){
	    if(exists(chunk["unset"]) && chunk["unset"] == 1){
		document.querySelectorAll('._interaction').forEach(e => e.remove());		
	    }
	    
	    if(exists(chunk["username"])){
		document.getElementById("data_username").innerText=chunk["username"];
		document.getElementById("set_username_nutzereinstellungen").value=chunk["username"];
		document.getElementById("set_username_frage_stellen").value=chunk["username"];
	    }
	    if(exists(chunk["session"])){
		document.getElementById("data_session").value=chunk["session"];
	    }
	    if(exists(chunk["level"]) && parseInt(chunk["level"]) >= 0 && parseInt(chunk["level"]) <= 3){
		chunk["level"]=parseInt(chunk["level"]);
		
		document.getElementById("set_level_0_frage_stellen").selected=false;
		document.getElementById("set_level_1_frage_stellen").selected=false;
		document.getElementById("set_level_2_frage_stellen").selected=false;
		document.getElementById("set_level_3_frage_stellen").selected=false;

		document.getElementById("set_level_0_nutzereinstellungen").selected=false;
		document.getElementById("set_level_1_nutzereinstellungen").selected=false;
		document.getElementById("set_level_2_nutzereinstellungen").selected=false;
		document.getElementById("set_level_3_nutzereinstellungen").selected=false;
		
		if(chunk["level"] == 0){
		    document.getElementById("set_level_0_frage_stellen").selected=true;
		    document.getElementById("set_level_0_nutzereinstellungen").selected=true;
		    document.getElementById("data_level").innerText="Anfänger";
		}
		if(chunk["level"] == 1){
		    document.getElementById("set_level_1_frage_stellen").selected=true;
		    document.getElementById("set_level_1_nutzereinstellungen").selected=true;
		    document.getElementById("data_level").innerText="Normaler Nutzer";
		}
		if(chunk["level"] == 2){
		    document.getElementById("set_level_2_frage_stellen").selected=true;
		    document.getElementById("set_level_2_nutzereinstellungen").selected=true;
		    document.getElementById("data_level").innerText="Fortgeschrittener";
		}
		if(chunk["level"] == 3){
		    document.getElementById("set_level_3_frage_stellen").selected=true;
		    document.getElementById("set_level_3_nutzereinstellungen").selected=true;
		    document.getElementById("data_level").innerText="Profi";
		}
	    }
	    if(exists(chunk["os"])){
		document.getElementById("data_os").innerText=chunk["os"];
		document.getElementById("set_os_frage_stellen").value=chunk["os"];
		document.getElementById("set_os_nutzereinstellungen").value=chunk["os"];
	    }
	    if(exists(chunk["mod"])){
		document.getElementById("data_mod_0").selected=false;
		document.getElementById("data_mod_1").selected=false;

		if(chunk["mod"] == 0){
		    document.getElementById("data_mod").innerText="User";
		    document.getElementById("data_mod_0").selected=true;
		    
		    document.querySelectorAll('._mod').forEach(e => e.remove());
		}
		if(chunk["mod"] == 1){
		    document.getElementById("data_mod").innerText="Moderator";
		    document.getElementById("data_mod_1").selected=true;

		    document.querySelectorAll('._not_mod').forEach(e => e.remove());
		}		
	    }
	}
    });
}

function exists(i){
    if(i === undefined){
	return false;
    }else{
	return true;
    }
}
