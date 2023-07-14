<?php
// you need to define your 
// $PASSWORD
// $LOCATION

session_start();
if(!(isset($_SESSION['hide']))) {
	$_SESSION['hide'] = 'display: none;';
}

if(isset($_POST['sub'])){
	if($_POST['password'] === ''){
		$_SESSION['verify'] = 'true';
		unset($_SESSION['passerror']);
		session_write_close();
		header('Location:index.php');
		die();
	} else {
		$_SESSION['passerror'] = 'invalid Login'; 
	}
}

// Clean an check entries if they fit 
function converti($prod, $meta) {
	$prod = substr($prod, 0, 120);
	$meta = str_replace('; ', ';', $meta);
	$parts = explode(';',$meta);
	$tim = time();
	$array['date'] = $tim;
	$array['prod'] = $prod;
	$array['stati'] = 'new';
	foreach ($parts as $val) {
		$array[] = $val ;
	}
	return json_encode($array);
}

// Append the entry to the DB
function appendi($newi, $keyi) {
	$shopdb = file_get_contents('shoppingdb');
	$dbdecode = json_decode($shopdb, true);
	$dbdecode[$keyi] = $newi;
	file_put_contents('shoppingdb', json_encode($dbdecode) );
}

// Change an entry only Status supported atm
function editi($action, $prod) {
	$workdb = json_decode(file_get_contents('shoppingdb'), true);
	foreach($workdb as $keys => &$vals) { 
		if ($keys == json_encode($prod)) {
			$vals= json_decode($vals, true);
			$vals['stati'] = $action;
			if ($action == 'purge') {
				unset($workdb[$keys]);
			}
			$vals= json_encode($vals);
		}
	}
	file_put_contents('shoppingdb', json_encode($workdb) );
}

// Catch the GET for adding entry then redirect
if(isset($_GET['product']) && $_GET['product'] != '') {
	$temp = json_encode($_GET['product']);
	$jsarti = converti($_GET['product'], $_GET['meta']);
	appendi($jsarti,$temp);
	header('Location:index.php');
} 
// Catch the GET for updating entry then redirect
if(isset($_GET['update']) && $_GET['update'] != '') {
	editi($_GET['update'],$_GET['prod']);
	header('Location:index.php');
}
// Catch the GET for manipualting the view from menu a
if(isset($_GET['menu']) && $_GET['menu'] != '') {
	switch ($_GET['menu']) {
		case "sort_time":
			$_SESSION['sort'] = 'date'; 
		break;
		case "sort_stati":
			$_SESSION['sort'] = 'stati'; 
		break;
		case "unhide":
			$_SESSION['hide'] = ''; 
		break;
		case "hide":
			$_SESSION['hide'] = 'display: none;'; 
		break;
		case "forget":
			session_unset(); 
		break;
	}
}
// Create the entries, pack the data in divs 
function displayentris() {
	$displaydb = json_decode(file_get_contents('shoppingdb'), true);
	$out ='';
	$helpsort = array();
	
	if(isset($_SESSION['sort']) && $_SESSION['sort'] == 'stati') {
		$sort_key = "stati";
	} else {
		$sort_key = "date";
	}
	foreach ($displaydb as $key => $row)
	{
		$row = json_decode($row,true);
		$helpsort[$key] = $row[$sort_key];
	}
	array_multisort($helpsort, SORT_DESC, $displaydb);

	if (!empty($displaydb)) {
		$out.= '<div class="entris_full">';
		foreach($displaydb as $keys => $vals) {
			$vals= json_decode($vals,true);
			$outpuffer= "";
			foreach($vals as $keyi => $metas) {
				switch ($keyi) {
					case "date":
						$displaydate = $metas;
					break;
					case "prod":
						$prodname = $metas;
					break;
					case "stati":
						$prodstatus = $metas;
					break;
					default:
						$outpuffer.= '<div class="btn entrismeta truncate">'.$metas.'</div>';
				}
			}
			$out.= '<div class="btn entris" id="src'.$displaydate.'" style="background-color:';
			switch ($prodstatus) {
				case "new":
					$out.= '#1abc9c';
					$showi = 'new';
				break;
				case "buyd":
					$out.= 'palegoldrodi; '.$_SESSION['hide'];
					$showi = 'bought';
				break;
				case "del":
					$out.= 'dimgrey; ' .$_SESSION['hide'];
					$showi = 'removed';
				break;
				case "redo":
					$out.= 'darkseagreen';
					$showi = 'rebuy';
				break;
			}			
			$out.= '" onclick="createSelect(\''.$displaydate.'\' ,actionis, \''.$prodname.'\');">';
				$out.= '<div class="entrishead truncate">'.$prodname.'</div>';
				$out.= '<div class="entrisdate">'.date("d.m.y",$displaydate).'</div>';
				$out.= '<div class="entrismetis">'.$outpuffer.'</div>';
				$out.= '<div class="entrisstatus" id="'.$displaydate.'"><span id="prost'.$displaydate.'">status: '.$showi.'</span></div>';
			$out.= '</div>';
		}
		$out.= '</div>';
	}
	echo $out;
}
function create_menu() {
	$menu ='';
	if (isset($_SESSION['verify']) && $_SESSION['verify'] != '') {
		$menu .='<div><button class="btn unfolder" id="unfolder" onclick="unfold_div(\'hidden_input\');">Don\'t forget your precious 😅️</button></div>
				<div><button class="btn setting" id="setting" onclick="unfold_div(\'hidden_menu\');">
			    	<span class="line"></span><span class="line"></span><span class="line"></span>
				</button></div>
				<div class="hidden_menu btn" id="hidden_menu">';
		if(isset($_SESSION['sort']) && $_SESSION['sort'] == 'stati') {
			$menu .= '<a href="?menu=sort_time">sort (time)</a>';
		} else {
			$menu .= '<a href="?menu=sort_stati">sort (status)</a>';
		}
		if(isset($_SESSION['hide']) && $_SESSION['hide'] == '') {
			$menu .= '<a href="?menu=hide">hide</a>';
		} else {
			$menu .= '<a href="?menu=unhide">unhide</a>';
		}
		$menu .='<a href="?menu=forget">forget me</a><a href="">info</a>';
	} else {
		$menu .='<div><button class="btn unfolder" id="unfolder" onclick="">Don\'t forget your precious 😅️</button></div>';	
	}
	return $menu;
}

// try to verify if the user has the credentials
function check_login () {
	if (isset($_SESSION['passerror'])) {
		$passError = $_SESSION['passerror'];
	} else {
		$passError = '';
	}
	if (isset($_SESSION['verify']) && $_SESSION['verify'] != '') {
		displayentris();	
	}
	else {
		$out = '';
		$out .= '<div id="login" class="login center btn"><form name="input" action="" method="post">
        <label for="password"></label><input type="password" value="" id="password" name="password" onClick="this.select();" />
        <div class="error">'.$passError.'</div><input type="submit" id="go" value="GO" class="btn" name="sub" /></form></div>';
		echo $out;
	}
}

// Create the html page and insert the entries from displayentris()
echo('
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>DOFO - Don\'t forget your precious 😅️</title>
	<meta name="description" content="Don\'t forget your precious 😅️">
	<meta name="author" content="Cheese Man">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="style.css">
</head>
<body>
 <div class="parent">
<div class="menu_bar" id="menu_bar">
		'.create_menu().'
	</div>
</div>
<div class="hidden_input" id="hidden_input">
<form action="" method="get" class="form-add">
  <div class="inputcontainer">
  <div class="inputfields">
	<div class="inputrow">
	  <label class="inputhead" for="name">What? </label>
	  <input class="inputcont" type="text" name="product" id="product" required>
	</div>
	<div class="inputrow">
	  <label class="inputhead" for="meta">Where? (use ; as separator) </label>
	  <input class="inputcont" type="text" name="meta" id="meta" optional>
	</div>
  <!-- div inputfields-->
  </div>
  <div class="inputbutton">
	<button class="btn inbutton" type="submit"> + </button>
  </div>
  <!-- div inputcontainer-->
  </div>
</form> 
<!-- div input-->
</div>

<div class="lists"> 
');
// the real entries generated my the function called after the login check
check_login();
echo('
</div>
<!-- div parent-->
</div> 
<script src="main.js"></script> 
</body>
');
