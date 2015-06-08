<?php
session_start();
ini_set('display_errors', 'On');

$db_username = 'williasc-db';
$db_password = 'qXQGn0fvpoKdYH26';
$db_name = 'williasc-db';
$db_host = 'oniddb.cws.oregonstate.edu';

$mysqli = new mysqli($db_host, $db_username, $db_password,$db_name);
if ($mysqli->connect_errno){
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

if(isset($_POST['u_name'])){
	$name = mysqli_real_escape_string($mysqli, $_POST['u_name']);
	$pass = mysqli_real_escape_string($mysqli, $_POST['u_pass']);

	$get_user = "SELECT * FROM username_list WHERE username='$name' AND userpassword='$pass'";
	$run_user = mysqli_query($mysqli, $get_user);
	$check = mysqli_num_rows($run_user);
	if ($check == 1){
		$_SESSION['username'] = $name;
		echo "<script>window.location.href = 'videoStore.php'</script>";
	}
	else{
		echo "<script>alert('Username or Password is incorrect')</script>";
		echo "<script>window.location.href = 'home.html'</script>";
	}
}


?>