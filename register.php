<?php
ini_set('display_errors', 'On');
echo '<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="utf-8">
    <title>Movie Database</title>
    <meta name="description" content="Movie Database">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="dashboard.css" rel="stylesheet">
    </head>
    <body>';


$db_username = 'williasc-db';
$db_password = 'qXQGn0fvpoKdYH26';
$db_name = 'williasc-db';
$db_host = 'oniddb.cws.oregonstate.edu';

$mysqli = new mysqli($db_host, $db_username, $db_password,$db_name);
if ($mysqli->connect_errno){
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

if(isset($_POST['username']) && isset($_POST['userpassword'] )){
	$stmt = $mysqli->prepare("INSERT INTO username_list(username,userpassword) VALUES (?,?)");
	$stmt->bind_param('ss', $_POST['username'], $_POST['userpassword']);
	$stmt->execute();
	$stmt->close();
}

$new_table = mysqli_real_escape_string($mysqli, $_POST['username']);
$sql = "CREATE TABLE ". $new_table ."(
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE,
        category VARCHAR(255),
        length INT(11),
        rented TINYINT(4)
        )";

$mysqli->query($sql);

echo "<script>window.location.href = 'videoStore.php'</script>";


echo "</body></html>";

?>