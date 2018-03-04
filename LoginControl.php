<?php
class login{
	private $username;
	private $password;
	
	public function __construct($length){
		if ($length > 0){
			$this->username = $_POST['username'];
			$this->password = $_POST['password'];
		} else {
			$this->username = null;
			$this->password = null;
		}
	}
	
	public function getUserName(){
		return $this->username;
	}
	
	public function getPassword(){
		return $this->password;
	}
	
	public function areInputsSet() {//check that input values have been assigned something 
		if (($this->username == null) || ($this->password == null)){
			if (($this->username == null) && ($this->password != null)) {
				return -1;//username NOT set
			} elseif (($this->username != null) && ($this->password == null)) {
				return -2;//password NOT set
			} else {	
				return 0;//BOT username/password NOT set
			}	
		} else {
			return 1;//username and password set
		}
	}
}

class DbConnection{
	//private $mysqli;
	public $mysqli;
	
	public function __construct(){
		$this->mysqli = mysqli_connect('localhost','root','','login');
	}

	public function checkConnection(){
		if(mysqli_connect_errno()){
			//echo 'Failed To connect'. $this->mysqli->connect_error();
			return mysqli_connect_error();
		} else {
			return 0;
		}
	}
	
	public function requestData($SqlQuery){
		return (($this->mysqli)->query($SqlQuery));
	}
	
	public function readData($SqlData){
		return ($SqlData->fetch_array());
	}
	
//	public function __destruct(){//close mysql connection
//		mysqli_close($this->mysqli);
//	} 
}

//$result = $mysqli->query("SELECT username,hash FROM userlogin WHERE username='$newLogin->getUserName()'");
//database table feilds = id, username, hash, first_name, last_name, email.

/*
if (($result->num_rows) == 0){
	//No username match, redirecting to register page
	//need to alert user that login failed!!!!!!!!!!!!!
	header('Location: register.php');
	exit();
} else {
	while(($row = $result->fetch_array()) != null){
		//username match, now checking against login password against hash returned from database
		if (password_verify(($newLogin->getPassword()),$row->hash)){
			header('location: task.php');
			exit();
		}
	}
	//if get to here, username MATCHED, password DIDN'T 
	header('Location: register.php');
	exit();
	
	
	
	$loginFailed= true;
}*/
//}
	//password_hash($password,PASSWORD_BCRYPT) return=string hash which is 60 characters long
	//password_verify($password,$hash)  inputs=strings,  return=boolean

	
?>	