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
	
	function tasksTableEmpty($dataBaseHandle,$username){
		$SqlQuery = "SELECT COUNT(*) FROM `login`.`$username`";
		$result = $dataBaseHandle->query($SqlQuery);
		if ($result->fetch() > 0){
			return false;
		} else {
			return true;
		}	
	}
	
	function getTasks($dataBaseHandle,$username,$taslId){
		$SqlQuery = "SELECT * FROM `login`.`$username` WHERE task_id=`$taskId`";
		$result = $dataBaseHandle->query($SqlQuery);
		return $result;
	}
	
function validate_date_time($datetime) {
	$date_time_valid = false;
	echo '<br> year'.$datetime['year'];
	echo '<br> month'.$datetime['month'];
	echo '<br> day'.$datetime['day'];
	//must be greater than 2017 and have format 2XXX
	if (($datetime['year'] > 2017) && preg_match('/2[0-1][0-9]{2}/',$datetime['year'])) {
		if (($datetime['month'] < 1) || ($datetime['month'] > 12)){
			messageBox('popUp','error','Month field of date isn\'t incorrect');
		} elseif (($datetime['day'] < 1) || ($datetime['day'] > cal_days_in_month(CAL_EASTER_DEFAULT,$datetime['month'],$datetime['year']))){
			//cal_days_in_month(calendar,month,year) determines max days in each month
			messageBox('popUp','error','Day isn\'t valid for this month');
		} else {
			$date_time_valid = validate_time($datetime['hour'],$datetime['minute']);
		}	
	} else {
		messageBox('popUp','error','Year field of date isn\'t incorrect');
	}	
	return $date_time_valid;
}
	
function validate_time($time_hour,$time_minute){
	$date_valid = false;
	if (($time_hour < 1) || ($time_hour > 12)){// must be 1 to 12
		messageBox('popUp','error','Hour entry isn\'t valid');
	} elseif (($time_minute < 0) || ($time_minute > 59)){// must be 1 to 59
		messageBox('popUp','error','Minute entry isn\'t valid');
	} else {
		$date_valid = true;
	}
	return $date_valid;
}
//============================IMPORTANT=================================================
//user created objects are destroyed when script finishes running (not persistant across)
//site pages so must create data base connection AGAIN to retrieve tasks!!!!!!!!!!!!!!!!
//======================================================================================
include 'LoginControl.php';
session_start();
$username = $_SESSION['username'];//isset();checks for a variable == to null


//$_POST['edittask'] = 'Edit tasks';//submit from page mytasks.php
//$_POST['addtask'] = 'Add Task';//submit from page addtask.php
//adding a new task dosen't post any of above

$newTask = true;
if (isset($_POST['edittask']) || isset($_POST['addtask']) || isset($_POST['error'])){
	//either edit existing task or problem with submitted new task.
	$currentDate = $_POST['date'];//scope??????????????????????????????????????????????????????????????????????????????????????????????
	//forms data encoded as "name=hh%3Amm", or "name=hh%3Amm%3ass if sec included\
	$currentTime = $_POST['time'];			
	$description = $_POST['description'];				
	$priority = $_POST['priority'];
	$newTask = false;
} else {
	//create new task 
	
	//this will return the current server time??????????????????????????????????
	//this value is 24hr based
	$currentTime = '12:00';//date("h:ia");
	//the value format of html date input is yyyy-mm-dd and the display date format
	//will be chosen based on the set locale of the users browser
	$currentDate = date("Y-m-d"); 
}



//yyyy-mm-dd
$date = preg_split('/[\D]+/',$currentDate);
function validate_date($date) {
	$date_valid = false;
	$days_in_month = 0;
	$month = array(1 => 'january','february','march','april','may','june','july','august','september','october','november','december');
	//$date = array(year[0],month[1],day[2])
	//must be greater than 2017 and have format 2XXX
	if (($date[0] > 2017) && preg_match('/2[0-1][0-9]{2}/',$date[0])) {
		if (($date[1] < 1) || ($date[1] > 12)){//month could be entered as sep
			messageBox('popUp','error','Unexpected month value. Enter 1 for january etc');
		} elseif (($date[2] < 1) || ($date[2] > ($days_in_month = cal_days_in_month(CAL_EASTER_DEFAULT,$date[1],$date[0])))){
			//cal_days_in_month(calendar,month,year) determines max days in each month
			messageBox('popUp','error','Expecting 1 to '.$days_in_month.' days for '.$month[$date[1]]);
		} else {
			$date_valid = true;
		}	
	} else {
		messageBox('popUp','error','Unexpected date format. Expecting 20yy-mm-dd');
	}	
	return $date_valid;
}

$error = false;
if (!isset($_POST['error'])){
	$error = validate_date($date);
	//if ($error === true){
	//	$error = validate_time($time);
	//}
}

$dataBaseHandle = null;
if (userDataBase($dataBaseHandle)){
			
} else {
	messageBox('popUp','warn','check data base connection');
}
//head first SQL pg 383
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
	<link rel="stylesheet" type="text/css" href="css/StylePopUps.css">
	<link rel="stylesheet" type="text/css" href="css/StyleAddTask.css">
</head>
<body id="top" >
	<!--top navigation bar-->
	<div class="nav-bar col-12">
		<a href="mytasks.php" class="nav-bar-button-enabled">myTask</a>
		<a href="index.php" class="nav-bar-button-enabled">Home</a>
		<a href="#top" class="nav-bar-button-enabled">Add List</a>
		<a href="addtask.php" class="nav-bar-button-enabled">Add Task</a>
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
					<label><b>Current status:</b></label>
					<h2>Logged in</h2>
					<input type="submit" class="loginbtn" value="Log Out"/>
				</div>	
			</form>
		</div><!--class="col-3"-->
		
		<!--main content-->
		<div class="col-9 main-content">
			<form class="task-form" action="addtask.php" method="post">
				<h1><?php echo ($newTask)?'Create task':'Modify task';?></h1>
				<h3 id="colourOne">Date</h3>
				<!--<input type="date" name="date" value=php echo>-->
				<input type="text" name="date" value=<?php echo $currentDate;?>>
				
				<!--class="error-year"	class="error-month"	class="error-day"-->
				
				<!--if type="time" forms data encoded as "name=hh%3Amm", or "name=hh%3Amm%3ass if sec included"-->
				<h3 id="colourTwo">Time</h3>
				<input type="time" name="time" value=<?php echo $currentTime;?> pattern="[0-9]{2}:[0-9]{2}">
				
				<h3 id="colourThree">Task Description</h3>
				<?php if ($newTask) :?>
					<textarea name="description"  placeholder="Enter description here" maxlength="30000"></textarea>
				<?php else :?>
					<textarea name="description" maxlength="30000"><?php echo $description;?></textarea>
				<?php endif; ?>
				
				<div id="priority">
					<h3 id="colourFour">Priority</h3>
					<div id="radio">
					<?php if ($newTask) : ?>	
						<input type="radio" name="priority" value="high"><label>High</label>
						<input type="radio" name="priority" value="medium" checked="checked"><label>Medium</label>
						<input type="radio" name="priority" value="low"><label>Low</label>
					<?php else :?>	
						<input type="radio" name="priority" value="high" <?php if ($priority == 'high'){echo "checked='checked'";}?>><label>High</label>
						<input type="radio" name="priority" value="medium" <?php if ($priority == 'medium'){echo "checked='checked'";}?>><label>Medium</label>
						<input type="radio" name="priority" value="low" <?php if ($priority == 'low'){echo "checked='checked'";}?>><label>Low</label>
					<?php endif; ?>	
					</div>
				</div>
				
				<?php if ($error === false) :?>
					<?php if ($_SESSION['form_type'] === 'popUp'): ?>
						<div id="popup-base" class="<?php echo $_SESSION['popup_type'];?>">
							<img src="images/information.png" alt="windows warning icon" style="width:64px;display:inline;">
							<h2 style="display:inline;"><?php echo $_SESSION['popup_message']; ?></h2>
							<input type="submit" value="OK" name="error">
						</div>
						<!--button that does nothing but display a button-->
						<button class="fakebtn">Add Task</button>
						<?php messageBox(null,null,null); ?>
					<?php else :?>
						<input type="submit" value="Add Task" name="addtask">  <!--$_POST[name] = value;-->
					<?php endif; ?>
				<?php else :?>
					<input type="submit" value="Add Task" name="addtask">  <!--$_POST[name] = value;-->
				<?php endif;?>	
				<!--<input type="submit" name="edittasks" value="Edit tasks">-->
				<!--$_POST['edittask'] = 'Edit tasks';$_POST[addtask] = Add Task;-->
			</form>
		</div>
	</div>
</body>
</html>