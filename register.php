<?php
//need to check that new registation dosen't match any currently in data base
//need to request SQL for username feild must be unique, not sure how to check password using 
//functions below?
//password_hash($password,PASSWORD_BCRYPT) return=string hash which is 60 characters long
//password_verify($password,$hash)  password=input [string],  return=boolean
	function messageBox($formType,$popupType,$message){
		$_SESSION['form_type'] = $formType;//popUp; | logIn
		$_SESSION['popup_type'] = $popupType;//$type = 'error', 'info', 'warn', 'success';
		$_SESSION['popup_message'] = $message;//$message= 'string describing type of error';
		$_SESSION['register_cancel'] = null;
	}
	
	function checkPassword($password,$password_confirm){
		$passwordsOk = false;
		if ($password == $password_confirm){
			if ($password == null){
				messageBox('popUp','error',"Password is a required field");
			} elseif (mb_strlen($password) >= 8){
				messageBox('popUp','success','Passwords ok');
				$passwordsOk = true;
			} else {
				messageBox('popUp','warn','Password must be at least 8 characters long');
			}
		} else {
			messageBox('popUp','error',"Password and confirmation don't match");
		}
		return $passwordsOk;
	}
	
	function checkRequiredFields($username,$password,$password_confirm){
		$requiredFieldsOk = false;
		if ($username != null){
			if (mb_strlen($username) >= 8) {
				$requiredFieldsOk = checkPassword($password,$password_confirm);
			} else {
				messageBox('popUp','warn','Username must be at least 8 characters long');
			}
		} else {
			messageBox('popUp','error','Username is a required field');
		}
		return $requiredFieldsOk;
	}	
	
	function checkOptionalFields(&$optionalInputs){
		//$AllowedCharacters = '/([@a-z0-9_-])+([@a-z0-9_-])$/i';//i = case insensitive
		$AllowedCharacters = '/[@a-z0-9_-]+$/i';//i = case insensitive
		$message = '';
		foreach($optionalInputs as $key => $value){
			if (($value === null) || preg_match($AllowedCharacters,$value)){
				continue;
			} else {
				$message .= $value.' ';	
			}
		}
		$words = preg_split('/\s/',$message,null,PREG_SPLIT_NO_EMPTY);

		$messageLength = sizeof($words);
		$formatMessage = '';
		if ($messageLength > 1){
			foreach($words as $key => $value){
				if ($key == ($messageLength - 1)) {//expecting array index to start at 0
					$formatMessage .= ' and '.$value;
				} elseif ($key >= 1) {
					$formatMessage .= ', '.$value;
				} else {
					$formatMessage .= $value;
				}
			}
		} elseif ($messageLength == 1) {
			$formatMessage = $words[0];
		} else {
			$formatMessage = null;
		}

		if ($formatMessage !== null) {
			messageBox('popUp','warn',$formatMessage.' must only contain characters<br> a to z, A to Z, -, @ and _');
			return false;
		} else {
			return true;
		}
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
	
	function testForUsername($dataBaseHandle,$username,&$usernameAvailable) {
		$SqlQuery = 'SELECT COUNT(username) FROM `userlogin` WHERE username=:username';
		$result = $dataBaseHandle->prepare($SqlQuery);
		$result->bindParam(':username',$username);
		$success = $result->execute();
		if (preg_match('/^00/',$result->errorCode())) {	
			if (($result->fetchColumn()) > 0){
				$usernameAvailable = false;
			} else {
				$usernameAvailable = true;
			}
		} else {
			$usernameAvailable = false;
		}
		$result = null;
		return $success;
	}
	
	function encryptPassword($dataBaseHandle,$username,$password,&$id){
		$security_hash = password_hash($password,PASSWORD_BCRYPT);
		$SqlQuery = "INSERT INTO `userlogin` (username,security_hash) VALUES (:username,:security_hash)";
		$result = $dataBaseHandle->prepare($SqlQuery);
		$result->bindParam(':username',$username);
		$result->bindParam(':security_hash',$security_hash);
		$result->execute();
		//if ($result->errorCode() === '/^OO/'){
		if (preg_match('/^00/',$result->errorCode())) {		
			$id = $dataBaseHandle->lastInsertId();
			$result = null;
			return true;
		} else {
			$id = null;
			$result = null;
			return false;
		}		
	}
	
	function createTasksTable($dataBaseHandle,$username) { 
		$SqlQuery = "CREATE TABLE `$username` (`task_id` INT AUTO_INCREMENT NOT NULL,
											   `date` DATE NULL,
											   `time` TIME NULL,
											   `description` VARCHAR(30000) NULL,
											   `priority` CHAR(6) NULL,
											   PRIMARY KEY(`task_id`))";
		$result = $dataBaseHandle->query($SqlQuery);	//prepare($SqlQuery);	
		if (preg_match('/^00/',$result->errorCode())){
			$result = null;
			return true;
		} else {
			$result = null;
			return false;
		}
	}

//how to do this without all this if/else nesting??????????????????????????????????????????????????????????????
session_start();
//suppose to arrive at this page via index.php
if ($_POST['register'] == 'Register') {//don't start until form submitted
	//get registration details strlng() mb_strlen();=length of string in characters,strlen()=length in bytes
	$first_name = empty($_POST['first_name'])?null:$_POST['first_name'];//
	$last_name = empty($_POST['last_name'])?null:$_POST['last_name'];
	$email = empty($_POST['email'])?null:$_POST['email'];
	$username = empty($_POST['username'])?null:($_POST['username']);//required can't have same user name!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	$password = empty($_POST['password'])?null:$_POST['password'];//required
	$password_confirm = empty($_POST['confirm'])?null:$_POST['confirm'];//required
	
	
	//check that required form registration fields are valid and at least 8 char in length
	$requiredFieldsOk = false;
	$requiredFieldsOk = checkRequiredFields($username,$password,$password_confirm);
	if ($requiredFieldsOk === true) {
		$optionalInputs = array('first_name'=>$first_name,'last_name'=>$last_name,'email'=>$email);
		if (checkOptionalFields($optionalInputs) === true) {
			//Start registration process
			$dataBaseHandle;
			if (userDataBase($dataBaseHandle) === true){//attempt data base connection
				$usernameAvailable = false;
				if (testForUsername($dataBaseHandle,$username,$usernameAvailable) === true){//attempt retrieval of username from data base
					//====================================================================================================================
					if ($usernameAvailable === true){
						$id = 0;
						if (encryptPassword($dataBaseHandle,$username,$password,$id) === true){//encryption and insertion into data base successful
							//insert into table user_details personal information. If this fails it's not that important
							$SqlQuery = "INSERT INTO `user_details` (first_name,last_name,email,id) VALUES (:first,:last,:email,:id)";
							$result = $dataBaseHandle->prepare($SqlQuery);
							$result->bindParam(':first',$first_name);
							$result->bindParam(':last',$last_name);
							$result->bindParam(':email',$email);
							$result->bindParam(':id',$id);
							$result->execute();
							
							//create table with same name as username to hold user tasks. Prioroty options available = low, medium, high
							if (createTasksTable($dataBaseHandle,$username) === true) { 
								$_SESSION['main_table_error'] = false;
							} else {
								$_SESSION['main_table_error'] = true;//error occured try this again in myTasks.php
							}
							messageBox('popUp','success','Login successful');
							$_SESSION['id'] = $id;
							$_SESSION['username'] = $username;
							$dataBaseHandle = null;
							header('Location:mytasks.php');
							exit();
						} else {
							messageBox('popUp','error','Password encryption error');
						}
					} else {
						messageBox('popUp','error','Your chosen username is unavailable.<br>Please try again.');
						echo 'message comming from here !!!!!!!!!!!!!!!!!!!!!!';
					}
				} else {
					messageBox('popUp','error','Data base connection error');
				}
			} else {//data base connection error message
				messageBox('popUp','error','Unable to connect to web server data base');
			}
		}
	}	
}	
//echo '<br>form type = '.$_SESSION['form_type'];
//echo '<br>popup type = '.$_SESSION['popup_type'];
//echo '<br>message = '.$_SESSION['popup_message'];
?>

<!DOCTYPE html>
<html>
<head>
	<title>Register with myTasks</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="css/Reset.css">
	<link rel="stylesheet" type="text/css" href="css/StyleGrid.css">
	<link rel="stylesheet" type="text/css" href="css/StyleNavBar.css">
	<link rel="stylesheet" type="text/css" href="css/StyleLogin.css">
	<link rel="stylesheet" type="text/css" href="css/StylePopUps.css">
	<link rel="stylesheet" type="text/css" href="css/StyleContent.css">
</head>
<body id="top">
	<!--top navigation bar-->
	<div class="nav-bar col-12">
		<a href="#top" class="nav-bar-button-disabled">myTask</a>
		<a href="index.php" class="nav-bar-button-enabled">Home</a>
		<a href="#top" class="nav-bar-button-disabled">Add List</a>
		<a href="#top" class="nav-bar-button-disabled">Add Task</a>
	</div>
	
	<!--all other content containment-->
	<div class="row">
		<!--left login pannel-->
		<div class="col-3">                 <!--onsubmit="validateInputs()" ??????????????????????-->
			<form class="form" action="index.php" method="post">
				<div class="imgcontainer">
					<img src="images/LoginPicture.jpg" alt="Login Picture" class="avatar" />
				</div>
				<div class="container">
					<label><b>Username</b></label>
					<input type="text" placeholder="Enter Username" name="username" required="required"/>
					<label><b>Password</b></label>
					<input type="password" placeholder="Enter Password" name="password" required="required"/>
					<input type="submit" class="loginbtn" value="Login"/>
				</div>	
			</form>
		</div><!--class="col-3"-->
			
		<!--main content-->
		<div class="col-9 main-content">
			<h1>Welcome to <b>myTasks</b></h1>
			<form class="registration" action="register.php" method="post">
				<fieldset>
					<legend> Registration form </legend>
					<table>
						<tr>
							<td><label for="first_name">First name:</label></td>
							<td><input type="text" placeholder="First name" name="first_name" id="first_name" maxlength="255"></td>
						</tr>	
						<tr>
							<td><label for="last_name">Last name:</label></td>
							<td><input type="text" placeholder="Last name" name="last_name" id="last_name" maxlength="255"></td>
						</tr>	
						<tr>
							<td><label for="email">Email: </label></td>
							<td><input type="text" placeholder="email" name="email" id="email" maxlength="255"></td>
						</tr>
						<tr>
							<td><label for="username">Username: </label></td>
							<td><input type="text" placeholder="Username" name="username" id="username" maxlength="255"></td><!--required="required"-->
						</tr>
						<tr>						
							<td><label for="password">Password: </label></td>
							<td><input type="password" placeholder="Password" name="password" id="password" maxlength="255"></td><!--required="required"-->
						</tr>
						<tr>
							<td><label for="confirm">Confirm password: </label></td>
							<td><input type="password" placeholder="Confirm password" name="confirm" id="confirm" maxlength="255"></td><!--required="required"-->
						</tr>
					</table>
					
					<!--<img src="antibotimpage.php" alt="Image created by a PHP script" width="200" height="80">-->
					
					<input type="submit" name="register" value="Register">
					<a href="register.php" class="cancelbtn">Cancel</a>
				</fieldset>
			</form>
		</div>
	</div>
	
	<!--error content, displayed to user when error occurs-->								
	<?php if ($_SESSION['form_type'] === 'popUp'): ?>
		<div id="popup-base" class="<?php echo $_SESSION['popup_type'];?>"><!--why aren't popup-base styles being applied????????????-->
		<!--didn't want to do this but onload just will NOT seem to function how I would like-->
			<h2><?php echo $_SESSION['popup_message']; ?></h2>
			<form action="register.php" method="post">
				<input type="submit" value="OK" name="ok">
			</form>	
		</div>
	<?php endif; ?>
	<?php messageBox(null,null,null); ?>
</body>
</html>