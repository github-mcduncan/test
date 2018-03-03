<?php

	function messageBox($formType,$popupType,$message){
		$_SESSION['form_type'] = $formType;//popUp; | logIn
		$_SESSION['popup_type'] = $popupType;//$type = 'error', 'info', 'warn', 'success';
		$_SESSION['popup_message'] = $message;//$message= 'string describing type of error';
		$_SESSION['register_cancel'] = null;
	}
	
	function userDataBase(&$dataBaseHandle) {
		$opt = [PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,//FETCH_OBJ,
				PDO::ATTR_EMULATE_PREPARES   => false];

		$dsn = 'mysql:host=localhost;dbname=login;charset=utf8';
		$user = 'root';
		$pass = 'simba12';
		$dataBaseHandle = new PDO($dsn,$user,$pass,$opt);
		$DataBase_error = $dataBaseHandle->errorInfo();
		
		if (preg_match('/^00/',$DataBase_error[0])){
			return true;
		} else {
			return false;
		}	
	}
	
	function get_display_preferences($dataBaseHandle,$username_id,&$display_preferences){//username[table].id[column]
		$SqlQuery = 'SELECT priority_order, datetime_order, delete_old, display_no
					FROM user_details
					WHERE '.$username_id.' = id';
		$result = $dataBaseHandle->query($SqlQuery);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		
		$display_preferences['display_no'] = (int)$row['display_no'];//five,ten,fifteen,tweenty or all [= display_no in database] [= dispalyNo in form]
		$display_preferences['priority'] = (int)$row['priority_order'];
		$display_preferences['date'] = (int)$row['datetime_order'];
		$display_preferences['delete'] = (int)$row['delete_old'];
		return;
	}
	
	function set_display_preferences($dataBaseHandle,$username_id,$display_preferences){//username[table].id[column]
		$SqlQuery = 'UPDATE user_details 
					SET priority_order = '.$display_preferences['priority'].', 
						datetime_order = '.$display_preferences['date'].', 
						delete_old = '.$display_preferences['delete'].',
						display_no = '.$display_preferences['display_no'].'
					WHERE '.$username_id.' = id';
		$dataBaseHandle->query($SqlQuery);
		return;
	}
	
	//COUNT(*) counts rows and not individual column values, ie COUNT(*) = number of table rows,
	//COUNT(column) = number of non-NULL values in column
	function CountTasks($dataBaseHandle,$username,$tab_tasks){//==true
		$list_select = '';
		if($tab_tasks == false){
			$list_select .= '_deleted';
		} else {
			$list_select .= '_sorted';
		}                                                                      
		$SqlQuery = 'SELECT COUNT(task_id) FROM login.'.$username.$list_select;//"SELECT COUNT(*) FROM `login`.`$username`";
		$result = $dataBaseHandle->query($SqlQuery);
		if (($task_count = $result->fetch()) > 0){
			return $task_count['COUNT(task_id)'];//??????????????????????????????????????????????????????????????????????????????????????
		} else {
			return 0;
		}	
	}
	
//	function CountTasks($dataBaseHandle,$username){
//		$SqlQuery = 'SELECT COUNT(task_id) FROM login.'.$username;//"SELECT COUNT(*) FROM `login`.`$username`";
//		$result = $dataBaseHandle->query($SqlQuery);
//		if (($task_count = $result->fetch()) > 0){
//			return $task_count['COUNT(task_id)'];//??????????????????????????????????????????????????????????????????????????????????????
//		} else {
//			return 0;
//		}	
//	}	
	
	function tab_display($tab_tasks){
		$list_select = '';
		if($tab_tasks == false){
			$list_select .= '_deleted';
		}
		return $list_select;
	}
	
	function getAllTasks($dataBaseHandle,$username,$tab_tasks){
		$SqlQuery = 'SELECT * FROM '.$username.tab_display($tab_tasks);
		echo '<br> getAllTasks = ',$SqlQuery;
		$result = $dataBaseHandle->query($SqlQuery);
	return $result;
	}
	
	//Check that page_index is a valid integer or string 'inc' or 'dec'.
	//This variable is passed via URL [ie $_GET] and therefore must be checked
	//that it is correct
	function check_index($page_index,$total_pages){
		echo '<br>is_integer($page_index) = '.is_integer($page_index);
		echo '<br>gettype() = '.gettype($page_index);
		if (is_integer($page_index) && ($page_index >= 1) && ($page_index <= $total_pages)) {//true,false
			return true;
		} else {
			return false;
		}
	}
	
	//determine which diplayable pages can be viewed for the chosen $page_index
	function setDisplayRange(&$range_limits,$total_pages,$page_index){
		define('PAGES_DISPLAYED',7);
		define('MAX_OFFSET',3);
		
		if ($total_pages <= constant('PAGES_DISPLAYED')){
			//range 1 to $total_pages
			$range_limits['min'] = 1;
			$range_limits['max'] = $total_pages;
		} else {//range $PAGE_NO_MAX buttons required
			//button moves, range is stationary
			if (($page_index <= constant('MAX_OFFSET')) || (($total_pages - $page_index) < constant('MAX_OFFSET'))) {
				if ($page_index <= constant('MAX_OFFSET')) {
					$range_limits['min'] = 1;
					$range_limits['max'] = constant('PAGES_DISPLAYED');
				} else {
					$range_limits['min'] = $total_pages - (constant('PAGES_DISPLAYED') - 1);
					$range_limits['max'] = $total_pages;
				}
			} else {//($page_index > $max_offset) && (($total_pages - $page_index) > $max_offset)
				//button stationary, range moves[$page_index +/- $max_offset] 
				$range_limits['min'] = $page_index - constant('MAX_OFFSET');
				$range_limits['max'] = $page_index + constant('MAX_OFFSET');
			}	
		}
		return;			
	}
	
	//
	function button_inc($page_index,$range_max){
		return ($page_index >= $range_max)?$range_max:($page_index + 1);
	}
	
	//
	function button_dec($page_index,$range_min){
		return ($page_index <= $range_min)?$range_min:($page_index - 1);
	}
	

	
	//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	//these are data base functions that I would have prefered to use an sql stored procedure for
	//got sick of stored procedure that wouldn't work see comment below closing php tag to see
	//stored procedure
	//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//	function get_display_flags(){
//		$display_flags = 0;
//		echo '<br>get_display_flags------------------------------------------------------------------';
//		echo '<br>$_SESSION[organizeBy] = '.print_r($_SESSION);
//		echo '<br>$_POST[] = '.print_r($_POST);
//		echo '<br>sizeof($_SESSION[organizeBy]) = '.sizeof($_SESSION['organizeBy']);
//		foreach ($_SESSION['organizeBy'] as $value) {
//			if ($value === 'priority'){
//				$display_flags = $display_flags + 1;
//			}
//			if ($value === 'date'){
//				$display_flags = $display_flags + 2;
//			}
//		}
//		return $display_flags;
//	}	
		
		
	//==============================================================================================================	
	//             IMPORTANT	
	//USERNAME	            TIMESTANP(8) CURRENT_TIMESTAMP = YYYYMMDD [tried this didn't work]
	//    		COLUMN date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,    use these defaults in create table for username
	//			COLUMN time TIME DEFAULT '12:00:00',               can't use CURRENT_DATE or time but can use timestamp
	//			COLUMN description VARCHAR(30000) DEFAULT 'none',  which includes both date and time
	//			COLUMN priority CHAR(6) DEFAULT 'low';	
	//
	//==============================================================================================================
	//                          php has an error control operator (@)
	//
	//when prepending(@) to an expression in php any error messages the might be generated by that expression will 
	//be ignored. If that line triggers an error, the error handler will still be called, but it will be called with
	//an error level of 0
	//
	//  $db_link = @mysql_connect($db_host,$db_username,$db_password);  //@ will prevent mysql_connect from returning 
	//errors
	//==============================================================================================================
	//    $_SESSION[] security learning php and mysql page=316
	//===============================================================================================================
	//sort order query
	function create_display_list($dataBaseHandle,$username,$display_preferences){
		$display_flags = $display_preferences['priority'] +	(2* $display_preferences['date']);
		//base query tried to do this via SQL stored procedure but nothing seemed to work see comment
		//below for stored procedure, constant error was 1051 unknow table, table did exist, don't know what I was doing wrong.
		//$SqlQuery = 'SELECT '.$username.'.*, priority_sort_order.order_id 
		//			FROM '.$username.',priority_sort_order 
		//			WHERE '.$username.'.priority = priority_sort_order.priority';	
		$SqlQuery = 'SELECT '.$username.'.*, priority_sort_order.order_id 
					FROM '.$username.' INNER JOIN priority_sort_order 
					ON '.$username.'.priority = priority_sort_order.priority';			
		//echo '<br>display_flags = '.$display_flags;
		
		try {//expectation is to reach catch block only if table username_sorted_tasks doesn't exist
			drop_display_view($dataBaseHandle,$username);
			$SqlQuery = 'CREATE VIEW '.$username.'_sorted_tasks AS '.$SqlQuery;
			$dataBaseHandle->query($SqlQuery);
		} catch (Exception $e) {	
			$SqlQuery = 'CREATE VIEW '.$username.'_sorted_tasks AS '.$SqlQuery;
			$dataBaseHandle->query($SqlQuery);
		}

		try {//expectation is to reach catch block only if table username_sorted doesn't exist
			drop_display_list($dataBaseHandle,$username);
			$SqlQuery = 'CREATE TABLE '.$username.'_sorted (
						id int NOT NULL AUTO_INCREMENT,
						task_id int,
						PRIMARY KEY (id),
						FOREIGN KEY (task_id) REFERENCES '.$username.'(task_id)
						)';
			$dataBaseHandle->query($SqlQuery);
		} catch (Exception $e) {
			$SqlQuery = 'CREATE TABLE '.$username.'_sorted (
						id int NOT NULL AUTO_INCREMENT,
						task_id int,
						PRIMARY KEY (id),
						FOREIGN KEY (task_id) REFERENCES '.$username.'(task_id)
						)';
			$dataBaseHandle->query($SqlQuery);
		}	
	
		$SqlQuery = 'INSERT INTO '.$username.'_sorted (task_id) SELECT task_id FROM '.$username.'_sorted_tasks';
		switch($display_flags) {
			//set display organization
			case 1: $SqlQuery .= ' ORDER BY '.$username.'_sorted_tasks.order_id';
					break;
			case 2: $SqlQuery .= ' ORDER BY '.$username.'_sorted_tasks.date, '.$username.'_sorted_tasks.time';
					break;
			case 3: $SqlQuery .= ' ORDER BY '.$username.'_sorted_tasks.order_id, '.$username.'_sorted_tasks.date, '.$username.'_sorted_tasks.time';
					break;
			default:$SqlQuery .= '';		
		}
			
		//echo '<br>SqlQuery = '.$SqlQuery;
		$dataBaseHandle->query($SqlQuery);
		$SqlQuery = null;
		return true;
	}	
	
/*	function create_display_list($dataBaseHandle,$username,$display_preferences){
		$display_flags = $display_preferences['priority'] +	(2* $display_preferences['date']);
		//base query tried to do this via SQL stored procedure but nothing seemed to work see comment
		//below for stored procedure, constant error was 1051 unknow table, table did exist, don't know what I was doing wrong.
		//$SqlQuery = 'SELECT '.$username.'.*, priority_sort_order.order_id 
		//			FROM '.$username.',priority_sort_order 
		//			WHERE '.$username.'.priority = priority_sort_order.priority';	
		$SqlQuery = 'SELECT '.$username.'.*, priority_sort_order.order_id 
					FROM '.$username.' INNER JOIN priority_sort_order 
					ON '.$username.'.priority = priority_sort_order.priority';			
		echo '<br>display_flags = '.$display_flags;
		switch($display_flags) {
			//set display organization
			case 1: $SqlQuery .= ' ORDER BY priority_sort_order.order_id';
					break;
			case 2: $SqlQuery .= ' ORDER BY '.$username.'.date,'.$username.'.time';
					break;
			case 3: $SqlQuery .= ' ORDER BY priority_sort_order.order_id,'.$username.'.date,'.$username.'.time';
					break;
		}
		
		try {//expectation is to reach catch block only if table username_sorted_tasks doesn't exist
			drop_display_view($dataBaseHandle,$username);
			$SqlQuery = 'CREATE VIEW '.$username.'_sorted_tasks AS '.$SqlQuery;
			$dataBaseHandle->query($SqlQuery);
		} catch (Exception $e) {	
			$SqlQuery = 'CREATE VIEW '.$username.'_sorted_tasks AS '.$SqlQuery;
			$dataBaseHandle->query($SqlQuery);
		}

		try {//expectation is to reach catch block only if table username_sorted doesn't exist
			drop_display_list($dataBaseHandle,$username);
			$SqlQuery = 'CREATE TABLE '.$username.'_sorted (
						id int NOT NULL AUTO_INCREMENT,
						task_id int,
						PRIMARY KEY (id),
						FOREIGN KEY (task_id) REFERENCES '.$username.'(task_id) ON DELETE CASCADE
						)';
			$dataBaseHandle->query($SqlQuery);
		} catch (Exception $e) {
			$SqlQuery = 'CREATE TABLE '.$username.'_sorted (
						id int NOT NULL AUTO_INCREMENT,
						task_id int,
						PRIMARY KEY (id),
						FOREIGN KEY (task_id) REFERENCES '.$username.'(task_id) ON DELETE CASCADE
						)';
			$dataBaseHandle->query($SqlQuery);
		}			
		$SqlQuery = 'INSERT INTO '.$username.'_sorted ('.$username.'_sorted.task_id) SELECT task_id FROM '.$username.'_sorted_tasks';
						//ORDER BY '.$username.'_sorted.id ASC';
		$dataBaseHandle->query($SqlQuery);
		$SqlQuery = null;
		return true;
	}	*/
	
	function create_delete_list($dataBaseHandle,$username,$display_preferences){
		$display_flags = $display_preferences['priority'] +	(2* $display_preferences['date']);
		//base query tried to do this via SQL stored procedure but nothing seemed to work see comment
		//below for stored procedure, constant error was 1051 unknow table, table did exist, don't know what I was doing wrong.
		
		$current_date = date("Y-m-d");//not using time as there are all sorts of timezone problems
		//would have to request time zone information from user. As forms really suck, don't want 
		//to keep requesting more and more info from user during registration.// AND ('.$username.'.time < '.$current_time.'))';
		//$SqlQuery = 'SELECT * FROM '.$username.' 
		//			WHERE (('.$username.'.priority IS NULL) OR ('.$username.'.date < "'.$current_date.'"))';	

		$SqlQuery = 'SELECT '.$username.'.*, priority_sort_order.order_id
					FROM '.$username.' INNER JOIN priority_sort_order
					ON '.$username.'.priority = priority_sort_order.priority
					WHERE (('.$username.'.priority IS NULL) OR ('.$username.'.date < "'.$current_date.'"))';
		
		try {
			drop_delete_view($dataBaseHandle,$username);
			$SqlQuery = 'CREATE VIEW '.$username.'_deleted_tasks AS '.$SqlQuery;
			$dataBaseHandle->query($SqlQuery);
		} catch (Exception $e) {	
			$SqlQuery = 'CREATE VIEW '.$username.'_deleted_tasks AS '.$SqlQuery;
			$dataBaseHandle->query($SqlQuery);
		}

		try {
			drop_delete_list($dataBaseHandle,$username);
			$SqlQuery = 'CREATE TABLE '.$username.'_deleted (
						id int NOT NULL AUTO_INCREMENT,
						task_id int,
						date DATE,
						time TIME,
						description VARCHAR(30000),
						priority CHAR(6),
						order_id int,
						PRIMARY KEY (id)
						)'; //FOREIGN KEY (task_id) REFERENCES '.$username.'(task_id) ON DELETE CASCADE
			$dataBaseHandle->query($SqlQuery);
		} catch (Exception $e) {
			$SqlQuery = 'CREATE TABLE '.$username.'_deleted (
						id int NOT NULL AUTO_INCREMENT,
						task_id int,
						date DATE,
						time TIME,
						description VARCHAR(30000),
						priority CHAR(6),
						order_id int,
						PRIMARY KEY (id)
						)';//FOREIGN KEY (task_id) REFERENCES '.$username.'(task_id) ON DELETE CASCADE
						//FOREIGN KEY (column of table in CREATE TABLE query) REFERENCES OtherTable(OtherTables column)
						//date DATETIME default CURRENT TIMESTAMP,[cuurent time automatically entered]
			$dataBaseHandle->query($SqlQuery);
		}			
		$SqlQuery = 'INSERT INTO '.$username.'_deleted (task_id, date, time, description, priority, order_id) 
					SELECT task_id, date, time, description, priority, order_id FROM '.$username.'_deleted_tasks';
					
		switch($display_flags) {
			//set display organization
			case 1: $SqlQuery .= ' ORDER BY '.$username.'_deleted_tasks.order_id';
					break;
			case 2: $SqlQuery .= ' ORDER BY '.$username.'_deleted_tasks.date,'.$username.'_deleted_tasks.time';
					break;
			case 3: $SqlQuery .= ' ORDER BY '.$username.'_deleted_tasks.order_id,'.$username.'_deleted_tasks.date,'.$username.'_deleted_tasks.time';
					break;
		}			
		$dataBaseHandle->query($SqlQuery);
		
		//==============================important======================================================
		//$SqlQuery = 'DELETE '.$username.' FROM '.$username.' INNER JOIN '.$username.'_deleted 
		//				ON '.$username.'_deleted.task_id = '.$username.'.task_id'; 
		//$dataBaseHandle->query($SqlQuery);
		
		$SqlQuery = null;
		return true;
	}
	
	//$username.'_deleted'  //holds deleted tasks
	//$username //holds current tasks
	
	function get_display_list($dataBaseHandle,$username,$index_id,$per_page,$get_deleted){
		//echo '<br>list index = '.$index_id;
		//$table = ($get_deleted)?$username.'_deleted':$username;

		$SqlQuery = '';
		if ($get_deleted == true){		
			$SqlQuery .= 'SELECT '.$username.'.*,'.$username.'_sorted.id 
						FROM '.$username.'_sorted 
						INNER JOIN '.$username.' ON ('.$username.'_sorted.task_id = '.$username.'.task_id) AND ('.$username.'_sorted.id > '.$index_id.') 
						LIMIT '.$per_page;			
		} else {
			$SqlQuery .= 'SELECT * FROM '.$username.'_deleted LIMIT '.$per_page;
		}			
		return $dataBaseHandle->query($SqlQuery);
	}
	
//	function get_display_list($dataBaseHandle,$username,$index_id,$per_page){
//		echo '<br>list index = '.$index_id;
//		$SqlQuery = 'SELECT '.$username.'.*,'.$username.'_sorted.id 
//					FROM '.$username.'_sorted 
//					INNER JOIN '.$username.' ON ('.$username.'_sorted.task_id = '.$username.'.task_id) AND ('.$username.'_sorted.id > '.$index_id.') 
//					LIMIT '.$per_page;
//		return $dataBaseHandle->query($SqlQuery);
//	}	

	function drop_display_view($dataBaseHandle,$username){
		$SqlQuery = 'DROP VIEW '.$username.'_sorted_tasks';
		$dataBaseHandle->query($SqlQuery);
		return;
	}
	
	function drop_display_list($dataBaseHandle,$username){
		$SqlQuery = 'DROP TABLE '.$username.'_sorted';
		$dataBaseHandle->query($SqlQuery);
		return;
	}	
	
	function drop_delete_view($dataBaseHandle,$username){
		$SqlQuery = 'DROP VIEW '.$username.'_deleted_tasks';
		$dataBaseHandle->query($SqlQuery);
		return;
	}
	
	function drop_delete_list($dataBaseHandle,$username){
		$SqlQuery = 'DROP TABLE '.$username.'_deleted';
		$dataBaseHandle->query($SqlQuery);
		return;
	}
	
	//clearing table will not recycle primary key id values, leeding to variable task_index
	function clear_display_list($dataBaseHandle,$username){
		$SqlQuery = 'DELETE FROM '.$username.'_sorted';
		$dataBaseHandle->query($SqlQuery);
		return;
	}
	//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	
//============================IMPORTANT=================================================
//user created objects are destroyed when script finishes running (not persistant across)
//site pages so must create data base connection AGAIN to retrieve tasks!!!!!!!!!!!!!!!!
//======================================================================================
//SHOULD THIS TABLE BE DIVIDED INTO ALPHABETICAL TABLES????????????????????????? 
//user attributes comming from TABLE user_details////
include 'LoginControl.php';
//======================================================================================
session_start();
//most sessions set a user-key on the users computer that looks something like this:
//765487cf34ert8dede5a562e4f3a7e12. then, when a session is opened on another page, it
//scans the computer for a user-key. If there is a match, it accesses that session, if
//not, it starts a new session.
//session vars are also another security risk and this needs looking into.

//??????????????????????????????????????????????????????????????????????????????????????
//if task display options are changed, then apply changes to results table and keep relavent
//boxes checked but don't keep appling changes. 
//???????????????????????????????????????????????????????????????????????????????????????


//comming from TABLE userlogin
$username = 'mcduncan';//$_SESSION['username'];//isset();checks for a variable == to null
$id = 74;

//ideally I would like $_SESSION to only hold the state/page that was being 
//displayed before requesting this page.	
$dataBaseHandle = null;
$fill_table = false;
$get_new_sort = false;
$_SESSION['page'] = 'mytasks';
if (userDataBase($dataBaseHandle)){
	if ($_SESSION['page'] != 'mytasks') {
		$get_new_sort = true;
		$_SESSION['page'] = 'mytasks';
	}
	
	$display_preferences = array('display_no'=>0,'priority'=>0,'date'=>0,'delete'=>0);
	if (array_key_exists('applyDisplayOpt',$_POST)) {
		$display_preferences['display_no'] = (int)isset($_POST['displayNo'])?$_POST['displayNo']:15;//five,ten,fifteen,tweenty or all [= display_no in database] [= dispalyNo in form]
		$display_preferences['priority'] = isset($_POST['priority'])?1:0;
		$display_preferences['date'] = isset($_POST['date'])?1:0;
		$display_preferences['delete'] = isset($_POST['delete'])?1:0;
		set_display_preferences($dataBaseHandle,$id,$display_preferences);
		$get_new_sort = true;
	} else {
		get_display_preferences($dataBaseHandle,$id,$display_preferences);
	}
	
	$tab_tasks = true;
	if(isset($_GET['tab']) && $display_preferences['delete']){
		if ($_GET['tab'] === 'deleted'){
			$tab_tasks = false;
		} 
	}
	
	echo '<br>+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++';
	echo '<br>display_preferences = '.print_r($display_preferences);
	echo '<br>get_new_sort = '.$get_new_sort;
	
	if ($get_new_sort == true){
		//http://code.openark.org/blog/mysql/mysqls-character-sets-and-collations-demystified 
		//[latin1 = local charset] ascii for first 128 characters/then 128 local charset characters. NOT international
		//generally the further along in the alphabet a character is the higher its value 'b' > 'a'[ = ture]
		if ($display_preferences['delete']) {
			create_delete_list($dataBaseHandle,$username,$display_preferences);
		}
		create_display_list($dataBaseHandle,$username,$display_preferences);//$display_preferences['display_no','priority','date','delete']
		$get_new_sort = false;
	}
	
	//$task_no = number of task entries in data base
	$tab_highlight = 0;
	if ($display_preferences['delete']){
		if(isset($_GET['tab'])){
			$tab_highlight = ($_GET['tab'] == 'deleted')?1:0; 
		}
	}	
	//$task_no = number of task entries in data base to display
	$task_no = CountTasks($dataBaseHandle,$username,$tab_tasks);
	echo '<br>task_no = '.$task_no;
	
	if ($display_preferences['display_no'] == 0) {
		$display_preferences['display_no'] = $task_no;
	}
	
	$fill_table = ($task_no)?true:false;
	echo '<br> fill table = ',$fill_table;
	if ($fill_table){
		//$total_pages = number of pages required to display all current tasks
		$total_pages = ceil($task_no/($display_preferences['display_no']));
		//page index = page to display $_GET['page'] returns a string value without (int) cast
		$page_index = (array_key_exists('page_index',$_GET))?((int)$_GET['page_index']):1;
		
		//has page_index been modified via URL?
		if (check_index($page_index,$total_pages) == false){
			$page_index = (int)$page_index;//in case page_index not integer
			if ($page_index < 1){
				$page_index = 1;
			} elseif ($page_index > $total_pages) {
				$page_index = $total_pages;
			}
		}
		
		//$range_limits = display pages in this range
		echo '<br> tab_tasks = '.$tab_tasks;
		$range_limits['min'] = 1;
		$range_limits['max'] = 1;
		if ($total_pages > 1){
			setDisplayRange($range_limits,$total_pages,$page_index);
			$task_index = $display_preferences['display_no'] * ($page_index - 1);
			echo '<br>display_preferences[display_no]'.$display_preferences['display_no'];
			echo '<br>page_index'.$page_index;
			//$taskTable = get_display_list($dataBaseHandle,$username,$task_index,$display_preferences['display_no']);
			$taskTable = get_display_list($dataBaseHandle,$username,$task_index,$display_preferences['display_no'],$tab_tasks);
		} else {
			$taskTable = getAllTasks($dataBaseHandle,$username,$tab_tasks);
		}
	} else {
		messageBox('popUp','info','No records are present');
	}
} else {
	messageBox('popUp','warn','check data base connection');
}
/*
mysql stored procedure that wouldn't work come back to this and see if I can get it to work
//but as of 1/2/18 I'm sick of sql
DELIMITER //
CREATE_DISPLAY_LIST(username varchar(255))//,display_flags,display_no)
BEGIN
 //DECLARE SQL_QUERY VARCHAR(1152) DEFAULT '0';
 //DECLARE TEST VARCHAR(1152) DEFAULT '0';
  SET @SQL_QUERY = CONCAT('CREATE VIEW ',username,'_sorted_tasks AS 
						  SELECT ',username,', priority_sort_order.order_id 
						  FROM ',username,' priority_sort_order 
						  WHERE priority_sort_order.priority = ',username,'.priority');

CASE display_flags
		WHEN 1 THEN
				SET @TEST = CONCAT(@SQL_QUERY,'ORDER BY priority_sort_order.order_id'); 
		WHEN 2 THEN
        		SET @TEST = CONCAT(@SQL_QUERY,'ORDER BY ',username,'.date,',username,'.time');
		WHEN 3 THEN
				SET @TEST = CONCAT(@SQL_QUERY,'ORDER BY priority_sort_order.order_id,',username,'.date,',username,'.time');
        ELSE   
        	SET @TEST = @SQL_QUERY;
	END CASE;
    
PREPARE stmt FROM @TEST;
    EXECUTE stmt;
    
  SET @TEST = CONCAT('CREATE TABLE ',username,'_sorted (id int NOT NULL AUTO_INCREMENT, task_id int, PRIMARY KEY (id))');  
    
    PREPARE stmt FROM @TEST;
    EXECUTE stmt;    

SET @TEST = CONCAT('INSERT INTO ',username,'_sorted (task_id) SELECT task_id FROM ',username,'_sorted_tasks');

PREPARE stmt FROM @TEST;
    EXECUTE stmt;   
DEALLOCATE PREPARE stmt;
END//

GET_DISPLAY_LIST(username,index)

DROP_DISPLAY_LIST()*/
?>

<!DOCTYPE html>
<html>
<head>
	<title>my Active Tasks</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="css/Reset.css">
	<link rel="stylesheet" type="text/css" href="css/StyleGrid.css">
	<link rel="stylesheet" type="text/css" href="css/StyleNavBar.css">
	<link rel="stylesheet" type="text/css" href="css/StyleLogin.css">
	<link rel="stylesheet" type="text/css" href="css/StyleDisplayOptions.css">
	<link rel="stylesheet" type="text/css" href="css/StylePopUps.css">
	<link rel="stylesheet" type="text/css" href="css/StyleTaskContent.css">
</head>
<body id="top"><!--still displaying myFunction before page loaded!!!!!!!!!!!!!!!!!!!!!!-->
	<?php if ($_SESSION['popup_type'] == 'success'): ?>
		<div id="popup-base" class="success">
		<!--didn't want to do this but onload just will NOT seem to function how I would like-->
			<h2><?php echo $_SESSION['popup_message'];?></h2>
			<form action="mytasks.php" method="post">
				<input type="submit" value="OK">
			</form>
		</div>
	<?php endif; 
		if (isset($_POST)){
			$_SESSION['form_type'] = null;
			$_SESSION['popup_type'] = null;
			$_SESSION['popup_message'] = null;
			$_SESSION['register_cancel'] = null;
		}
	?>
	
	<!--top navigation bar-->
	<div class="row nav-bar col-12">
		<a href="mytasks.php" class="nav-bar-button-enabled">myTask</a>
		<a href="index.php" class="nav-bar-button-enabled">Home</a>
		<a href="#top" class="nav-bar-button-enabled">Add List</a><!--this button dosen't make any sense-->
		<a href="addtask.php" class="nav-bar-button-enabled">Add Task</a>
		<span>Hi <?php echo $username; ?></span>
	</div>
	
	<!--all other content containment-->
	<div class="row">
		<!--left login pannel-->
		<div class="col-3">                 <!--onsubmit="validateInputs()" ??????????????????????-->
			<form class="form" action="index.php" method="post">
				<div class="imgcontainer">
					<img src="images/OpenLock.png" alt="Login Picture" class="avatar" />
				</div>
				<div class="container">
					<label><b>Current status: Logged in</b></label>
					<input type="submit" class="loginbtn" value="Log Out"/>
				</div>	
			</form>
			
			<form class="display-options" action="mytasks.php" method="post">
				<section>
					<h2>Task display options</h2>
				
					<!--display number of tasks per page-->
					<p>Tasks per page
					<select name="displayNo"><!--display tasks per page--><!--if session != to any option?-->
						<?php
							$select = 0;
							switch($display_preferences['display_no']){
								case 5:$select = 1;
									 break;
								case 10:$select = 10;
									 break;
								case 15:$select = 100;
									 break;
								case 20:$select = 1000;
									 break;
								case 40:$select = 10000;
									 break;
								default:$select = 100000;
									 break;	
							}		 
							$priority_checked = ($display_preferences['priority'])?true:false;
							$date_checked = ($display_preferences['date'])?true:false;
							$removal_checked = ($display_preferences['delete'])?true:false;		 
						?>
						<option value="5" <?php if($select == 1){echo " selected='selected'";} ?>>5</option><!--selected="selected"-->
						<option value="10" <?php if($select == 10){echo " selected='selected'";} ?>>10</option>
						<option value="15" <?php if($select == 100){echo " selected='selected'";} ?>>15</option>
						<option value="20" <?php if($select == 1000){echo " selected='selected'";} ?>>20</option>
						<option value="40" <?php if($select == 10000){echo " selected='selected'";} ?>>40</option>
						<option value="all" <?php if($select == 100000){echo " selected='selected'";} ?>>All tasks</option>
					</select><br><!--in php (int)$string = 0 [if($_SESSION['displayNo'] == 5)]-->
					</p>
					
					<!--organize by priority-->
					<label><input type="checkbox" name="priority" value="yes" <?php if($priority_checked){echo " checked='checked'";}?>>Order by priority</label><br>
					<!--organize by date-->
					<input type="checkbox" name="date" value="yes" <?php if($date_checked){echo " checked='checked'";}?>>Order by date/time<br>
					<!--delete old tasks automatically-->
					<input type="checkbox" name="delete" value="yes" <?php if($removal_checked){echo " checked='checked'";}?>>Automate old task removal<br>
					<!--apply display options to displayed tasks-->	
					<input type="submit" name="applyDisplayOpt" value="Apply">
				</section>	
			</form>
		</div><!--class="col-3"-->
		
		<!--main content-->
		<div class="col-9 task-content">
			<!--<div id="task-content">-->
			<h1>Current Tasks</h1>
			<a href="?addtask=true" class="add-task">Add Task</a>
			<div style="position:relative;top:0;right:0;width:1em;">
				<div id="mySidenav" class="sidenav">
					<a href="#" id="blog">Blog</a>
					<a href="#" id="projects">Projects</a>
					<a href="#" id="contact">Contact</a>
				</div>
			</div>
			
			<!--<h2>Here are your current lists</h2>-->
			<div class="tabs">
				<a class="<?php if (! $tab_highlight){echo 'selected';} ?>" href="?tab=current">Current Tasks</a>
				<a class="<?php if ($tab_highlight){echo 'selected';} ?>" href="?tab=deleted">Deleted Tasks</a>
			</div>
			
			<?php if ($fill_table) { ?>
				<form action="addtask.php" method="post">
					<?php while (($row = $taskTable->fetch(PDO::FETCH_ASSOC)) !== false): ?> 
					<div class="task <?php echo $row['priority'];?>">
						<div class="left-col">
							<h3>Task <em><?php echo $row['task_id']; ?></em></h3>
							<?php if ($tab_highlight):?>
								<input type="checkbox" name="edit[]" value=" <?php $row['task_id'];?>">delete<br>
							<?php else :?>	
								<input type="checkbox" name="edit[]" value=" <?php $row['task_id'];?>">edit<br>
							<?php endif;?>
						</div>
						<div class="right-col">
							<div class="param">
								<span><?php echo 'Date: '.$row['date'];?></span>
								<span><?php echo 'Time: '.$row['time'];?></span>
								<span><?php echo 'Priority '.$row['priority'];?></span>
							</div>
							<div class="descript">
								<p><?php echo 'Description: ';?></p>
								<p><?php echo $row['description'];?></p>
							</div>
						</div>
					</div>
					<?php endwhile;?>
					
					<!--navigation buttons to view task pages ie, << 1 2 3 4 >>-->
					<!--if total_pages = 1 << and >> links not needed-->
					<div id="page-nav">
						<?php $select_tab = ($tab_tasks == false)?'deleted':'tasks'; ?>
						<!-- << [dec button]-->
						<?php if ($total_pages > 1) : ?>
							<?php if ($page_index == 1){?>
								<!--if already displaying page 1, link does nothing-->
								<a class="page-btn radius-left cursor-default">&lt&lt</a>
							<?php }else {?>
								<a class="page-btn radius-left" href="?page_index=<?php echo button_dec($page_index,$range_limits['min']).'&tab='.$select_tab;?>">&lt&lt</a>
							<?php }?>	
						<?php endif;?>
						
						<!--selection buttons within and including range 'min' to 'max'-->
						<?php for ($i = $range_limits['min'];$i <= $range_limits['max'];$i++) :?><!--change appearence of button when current page-->
							<!--href='$_SERVER['PHP_SELF']?var=-->
							<?php if ($i != $page_index) {?>
								<a class="page-btn" href="?page_index=<?php echo $i.'&tab='.$select_tab;?>"><?php echo $i;?></a>
							<?php } else {?>
								<!--if link to current page, link does nothing-->
								<a class="page-btn index cursor-default"><?php echo $i;?></a>
							<?php } ?>
						<?php endfor; ?>
						
						<!-- >> [inc button]-->
						<?php if ($total_pages > 1) :?>
							<?php if($page_index == $total_pages) {?>
								<!--if already displaying last page, link does nothing-->
								<a class="page-btn radius-right cursor-default">&gt&gt</a>
							<?php } else {?>	
								<a class="page-btn radius-right" href="?page_index=<?php echo button_inc($page_index,$range_limits['max']).'&tab='.$select_tab;?>">&gt&gt</a>
							<?php } ?>	
						<?php endif; ?>
					</div>
					
					<input type="submit" name="edittasks" value="Edit tasks">
				</form>
			<?php } else {?>
				<h3>Tasks table is empty</h3>
			<?php } ?>

			<?php if(isset($_GET['addtask']) && ($_GET['addtask'] == 'true')): ?>
			<!--having to use style here because saving css changes won't show up under google sources-->
			<div class="task-popup" style="position:fixed;right:0;top:30%;background:white;border:1px solid black;">
				<h1>message from task pop up</h1>
				<input type="text">
				<a class="selected" href="?addtask=false">close</a>
				<input type="submit" value="test">
			</div>
			<?php endif; ?>
		<!--</div> end main content	-->		
		</div>
	</div>
</body>
</html>