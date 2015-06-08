<?php
ini_set('display_errors', 'On');

$db_username = 'williasc-db';
$db_password = 'qXQGn0fvpoKdYH26';
$db_name = 'williasc-db';
$db_host = 'oniddb.cws.oregonstate.edu';


$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "williasc-db", "qXQGn0fvpoKdYH26", "williasc-db");
if ($mysqli->connect_errno){
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

if(isset($_POST['action']) && $_POST['action'] == 'availability')
    {
        $username = mysqli_real_escape_string($mysqli,$_POST['username']); // Get the username values
        $query = "SELECT username from username_list where username='".$username."'";
        $res = mysqli_query($mysqli,$query);
        $count = mysqli_num_rows($res);
        echo $count;
    }


?>