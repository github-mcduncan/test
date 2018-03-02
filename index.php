<?php
	include 'LoginControl.php';
	session_start();
	//print_r(PDO::getAvailableDrivers());//print PDO data base drivers on system 
	//phpinfo();
	
	//used to print out session vars to screen during debugging
	function test() {
		echo '<br> form type'.$_SESSION['form_type'];
		echo '<br> popup type'.$_SESSION['popup_type'];
		echo '<br> message type'.$_SESSION['popup_message'];
		echo '<br> register cancel'.$_SESSION['register_cancel'];
	}
	
	function popUpMessage($type,$message){
		$_SESSION['form_type'] = 'popUp';//popUp; | logIn
		$_SESSION['popup_type'] = $type;//$type = 'error', 'info', 'warn', 'success';
		$_SESSION['popup_message'] = $message;//$message= 'string describing type of error';
		$_SESSION['register_cancel'] = null;
	}
				
	function verifyUser($newLogin,$result){
		$password = $newLogin->getPassword();
		while($row = $result->fetch(PDO::FETCH_ASSOC)){
			//username match, now checking against login password against hash returned from database
			echo '<br>testing password';
			if (password_verify($password,$row['security_hash'])){
				$_SESSION['username'] = $newLogin->getUserName();
				$_SESSION['dbh'] = $DataBaseHandle;
				$_SESSION['user_id'] = $row['id'];
				$result = null;
				$DataBaseHandle = null;//closes connection
				popUpMessage('success','login successful');
				header('location: mytasks.php');
				exit();
			} 
		}	
		//No password match for username if this point is reached
		popUpMessage('info','Oops an error occured, please try again');
		$result = null;
		$DataBaseHandle = null;//closes connection
	}//end function verifyUser($mysqli,$newLogin,$result)
	
	if ((count($_POST) > 0) && isset($_POST['login'])){//$_SESSION['form_type'] == 'logIn')) {//isset($_POST)
		//start logIn validation process
		$newLogin = new login(count($_POST) > 0);
		$loginStatus = $newLogin->areInputsSet();
		if ($loginStatus > 0) {//chack that login inputs have been set
			
			//=========================================================================================
			//turn off warning reporting as will be displaying a prompt to user
			error_reporting(E_ALL & ~E_WARNING);
			//=========================================================================================
			
			try{
				//debugging stuff---------------------------------------------
				error_reporting(E_ALL);
				ini_set('display_errors', '1');
				//debugging stuff---------------------------------------------
				$opt = [PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,//FETCH_OBJ,
						//this tells PDO to disable emulated prepared statements and use real prepared statements
						PDO::ATTR_EMULATE_PREPARES   => false]; 

				$dsn = 'mysql:host=localhost;dbname=login;charset=utf8';
				$user = 'root';
				$pass = 'simba12';
				$DataBaseHandle = new PDO($dsn,$user,$pass,$opt);
				$DataBase_error = $DataBaseHandle->errorInfo();
				if ($DataBase_error[0] == '00000') {
					//inputs ok complete login=========================================================================
					$SqlQuery = "SELECT id,security_hash FROM `userlogin` WHERE username=:username";	//$username'";				
					$username = $newLogin->getUserName();			
					$result = $DataBaseHandle->prepare($SqlQuery);
					$result->bindParam(':username',$username);
					$result->execute();
					//echo '<br> username = '.$username;
					//echo '<br> returned rows = '.$result->rowCount();
		
					if ($result){//username query returned the following passwords to check
						verifyUser($newLogin,$result);
					} else {//username query results in no registration table entries, forgotten password?
						$_SESSION['register_cancel'] = 'register_cancel';
						$_SESSION['form_type'] = 'popUp';//popUp | logIn
						$_SESSION['popup_type'] = 'info';//'login-error';//'login-info', 'login-warn', 'login-success';
						$_SESSION['popup_message'] = 'Oops an error occured, try again or if not a member please register';
						$result = null;
						$DataBaseHandle = null;//closes connection
					}
					//================================================================================================
				} else {//connection error couldn't connect to data base
					$message_string_a = 'Unable to connect to database, please try again';
					$message_string_b = '<br/><br/>SQLSTATE code = ,'.$DataBase_error[0];
					popUpMessage('warn',($message_string_a.$message_string_b));	
				}
				//return;
			} catch (PDOException $e){//catch block is not running!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				//var_dump($e);
				echo $e->getMessage();
				//echo '<br> message is comming for here';
				if (isset($result)){
					$result = null;//closes connection
				}
				popUpMessage('warn','Database error occurred, please try again');
				$DataBaseHandle = null;//closes connection
			}			
		} else {//login details not complete
			switch ($loginStatus) {
				case -1:
					popUpMessage('error','Username is a required field');
				break;
				case -2 :
					popUpMessage('error','Password is a required field');
				break;
				default:
					popUpMessage('error','Both Username and Password are required fields to login');
			}
		}	
	} else {
		$_SESSION['form_type'] = null;//popUp | logIn
		$_SESSION['popup_type'] = null;//'login-error';//'login-info', 'login-warn', 'login-success';
		$_SESSION['popup_message'] = null;//string describing type of error
		$_SESSION['register_cancel'] = null;
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>myTasks</title>
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
		<a href="#top" class="nav-bar-button-enabled">Home</a>
		<a href="#top" class="nav-bar-button-disabled">Add List</a>
		<a href="#top" class="nav-bar-button-disabled">Add Task</a>
	</div>
	
	<!--all other content containment-->
	<div class="row">
		<!--left login pannel-->
		<div class="col-3">                 <!--onsubmit="validateInputs()" ??????????????????????-->
			<form class="form" action="index.php" method="post">
				<div class="imgcontainer">
					<img src="images/LoginPicture.jpg" alt="Login Picture" class="avatar">
				</div>
				<div class="container">
					<label><b>Username</b></label>
					<input type="text" placeholder="Enter Username" name="username" required="required">
					<label><b>Password</b></label>
					<input type="password" placeholder="Enter Password" name="password" required="required">
					<input type="submit" class="loginbtn" value="Login" name="login">
				</div>	
				<div class="container">
					<a href="index.php" class="cancelbtn">Cancel</a>
					<span class="psw">Forgot <a href="#">password?</a></span>
				</div>
			</form>
			
			<div class="notmember">
				<h2>OR</h2>
				<a href="register.php" class="registerbtn">Register</a>
			</div>
		</div><!--class="col-3"-->
		
		<!--main content-->
		<div class="col-9 main-content">
			<h1>Welcome to <b>myTasks</b></h1>
			<p>myTasks is a small but helpful application where you can create and manage tasks to make your life easier. Just login and you can start adding tasks.</p>
		</div>
	</div>
	
	<!--error content, displayed to user when error occurs-->								
	<?php if ($_SESSION['form_type'] == 'popUp'): ?>
		<div id="popup-base" class="<?php echo $_SESSION['popup_type'];?>"><!--why aren't popup-base styles being applied????????????-->
		<!--didn't want to do this but onload just will NOT seem to function how I would like-->
			<h2><?php echo $_SESSION['popup_message']; ?></h2>
			<?php if ($_SESSION['register_cancel'] == 'register_cancel'): ?>
				<form action="register.php" method="post">
					<a href="index.php">cancel</a>
					<input type="submit" value="Register" name="register">
				</form>
			<?php else: ?>
				<form action="index.php" method="post">
					<input type="submit" value="OK" name="ok">
				</form>	
			<?php endif;?>
		</div>
	<?php endif; ?>
</body>
</html>