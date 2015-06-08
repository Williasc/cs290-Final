<?php
ini_set('display_errors', 'On');
session_start();
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
    <body>  

  <nav class="navbar navbar-inverse">
    <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <form class="navbar-form navbar-right" role="button" action="home.html" method="POST">
        <button type="submit" class="btn btn-default">Logout</button>
      </form>
      <!-- <ul class="nav navbar-nav">
        <li class="active"><a href="1.html">Home <span class="sr-only">(current)</span></a></li>
      </ul> -->
    </div>
  </nav>';



$dbName = $_SESSION['username'];

$filePath = explode('/', $_SERVER['PHP_SELF'], -1);
$filePath = implode('/',$filePath);
$addMovie = "http://" . $_SERVER['HTTP_HOST'] . $filePath . "/PDOvideoStore.php?addMovie=1";
$browseCategory = "http://" . $_SERVER['HTTP_HOST'] . $filePath . "/PDOvideoStore.php?browse=1";
$delAll = "http://" . $_SERVER['HTTP_HOST'] . $filePath . "/PDOvideoStore.php?delAll=1";

function getRentAdd($id){
	global $filePath;
	$rentAdd = "http://" . $_SERVER['HTTP_HOST'] . $filePath . "/PDOvideoStore.php?rentID=" . $id;
	return $rentAdd;
}

function getDelAdd($id){
	global $filePath;
	$delAdd = "http://" . $_SERVER['HTTP_HOST'] . $filePath . "/PDOvideoStore.php?deleteID=" . $id;
	return $delAdd;
}

$pdo = new PDO("mysql:host=oniddb.cws.oregonstate.edu; dbname=williasc-db", "williasc-db", "qXQGn0fvpoKdYH26");


if(isset($_GET['addMovie']) && $_GET['addMovie'] == 1){
	$pdo->prepare("INSERT INTO videoDb(name,category,length) VALUES (:name, :category, :length)");
	$pdo->execute(array(
		":name" => $_POST['name'],
		":category" => $_POST['category'],
		":length" => $_POST['length']
	));
}

if(isset($_GET['delAll']) && $_GET['delAll'] == 1){
	$pdo->prepare("TRUNCATE TABLE videoDb");
	$pdo->execute();
}

if(isset($_GET['rentID'])){
	$checkOut = $_GET['rentID'];
	$pdo->prepare("SELECT rented FROM videoDb WHERE id = :id");
	$pdo->execute(array(":id" => $checkOut));
	$available = $pdo->fetch();
	if ($available == 1){
		$pdo->prepare("UPDATE videoDb SET rented = :newRent WHERE id = " . $checkOut);
		$newRent = 0;
		$pdo->execute(array(":newRent" => $newRent));
	}
	else{
		$pdo->prepare("UPDATE videoDb SET rented = :newRent WHERE id = " . $checkOut);
		$newRent = 1;
		$pdo->execute(array(":newRent" => $newRent));
	}
}

if(isset($_GET['deleteID'])){
	$delID = $_GET['deleteID'];
	$pdo->prepare("DELETE FROM videoDb WHERE id = :delId");
	$pdo->execute(array(":delId" => $delID));
}


function createTable(){
	global $pdo;
	$pdo->query("SELECT id, name, category, length, rented FROM videoDb");

	$out_id		= NULL;
	$out_name	= NULL;
	$out_cat	= NULL;
	$out_length	= NULL;
	$out_rented	= NULL;
	list($out_id, $out_name, $out_cat, $out_length, $out_rented) = $pdo->fetchAll(PDO::FETCH_NUM);

	echo "<table class='table table-striped'>";
	echo "<thead><tr><th>ID</th><th>Title</th><th>Category</th><th>Length</th><th>Rent Status</th><th>Check Out/In</th><th>Delete</th></tr></thead>";
	while ($pdo->fetchAll()) {
		if($out_rented == 0){
			$out_rented = "Available";
			$rentStatus = "Check out";
		}
		else{
			$out_rented = "Checked Out";
			$rentStatus = "Return";
		}
		$rentAdd = getRentAdd($out_id);
		$delMovie = getDelAdd($out_id);
	    echo "<tr><td>" . $out_id . "</td><td>" . $out_name . "</td><td>" . $out_cat . 
	    	 "</td><td>" . $out_length . "</td><td>" . $out_rented . "</td><td>
	    	 <form id = 'rentForm' method = 'POST' action = '" . $rentAdd . "' name = 'rentMovie'>
	    	 <button class='btn btn-md btn-primary' type = 'submit'>" . $rentStatus . "</button></form></td><td>
	    	 <form id = 'deleteForm' method = 'POST' action = '" . $delMovie . "' name = 'delMovie'>
	    	 <button class='btn btn-md btn-primary' type = 'submit'>Delete Movie</button></form></td></tr>";
	}
	echo "</table>";
}

function createBrowseTable($browseCategory){
	global $pdo;
	$pdo->prepare("SELECT * FROM videoDb WHERE category = :category");
	$pdo->execute(array(":category" => $browseCategory));

	$out_id		= NULL;
	$out_name	= NULL;
	$out_cat	= NULL;
	$out_length	= NULL;
	$out_rented	= NULL;
	list($out_id, $out_name, $out_cat, $out_length, $out_rented) = $pdo->fetch(PDO::FETCH_NUM);

	echo "<table class='table table-striped'>";
	echo "<thead><tr><th>ID</th><th>Title</th><th>Category</th><th>Length</th><th>Rent Status</th><th>Check Out/In</th><th>Delete</th></tr></thead>";
	while ($pdo->fetch()) {
		if($out_rented == 0){
			$out_rented = "Available";
			$rentStatus = "Check out";
		}
		else{
			$out_rented = "Checked Out";
			$rentStatus = "Return";
		}
		$rentAdd = getRentAdd($out_id);
		$delMovie = getDelAdd($out_id);
	    echo "<tr><td>" . $out_id . "</td><td>" . $out_name . "</td><td>" . $out_cat . 
	    	 "</td><td>" . $out_length . "</td><td>" . $out_rented . "</td><td>
	    	 <form id = 'rentForm' method = 'POST' action = '" . $rentAdd . "' name = 'rentMovie'>
	    	 <button type = 'submit'>" . $rentStatus . "</button></form></td><td>
	    	 <form id = 'deleteForm' method = 'POST' action = '" . $delMovie . "' name = 'delMovie'>
	    	 <button type = 'submit'>Delete Movie</button></form></td></tr>";
	}
	echo "</table>";
}




echo "<div class = 'container-fluid'>
		<div class='row'>
			<div class='col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main'>
				<form action ='" . $addMovie . "' method='post'>
					
					<table class='table'>
						<tr>
							<td align='right'>Title:</td>
							<td>
							<input type='text' name='name' placeholder='Enter a movie title' required>
							</td>
						</tr>
						<tr>
							<td align='right'>Category:</td>
							<td>
							<input type='text' name='category' placeholder='Enter a category'>
							</td>
						</tr>
						<tr>
							<td align='right'>Movie Length:</td>
							<td>
							<input type='number' min='0' name='length'>
							</td>
						</tr>
						<tr>
							<td align='right'>
							<button class='btn btn-md btn-primary' type = 'submit' value='Add movie'>Add Movie</button>
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
	</div>";

	echo "<div class = 'container-fluid'>
			<div class='row'>
				<div class='col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main'>
					<form action ='" . $browseCategory . "' method='post'>
						<table>
							<tr>
								<td align='right'>Browse movies by Category: </td>
								<td>
								<select name = 'dropCategory'>
									<option value='all'>Select All</option>";
									$res = $pdo->query("SELECT DISTINCT category FROM videoDb");
									for ($row_no = 0; $row_no < $res->num_rows; $row_no++){
										$row = $res->fetch_assoc();
										echo "<option value='" . $row['category'] . "'>" . $row['category'] . "</option>";
									}
								echo "</select>
								</td>
								<td>
								<button class='btn btn-sm btn-primary' type = 'submit' value='Browse'>Browse</button>
								</td>
					</form>
					<td><form action = '" . $delAll . "' method='post'>
						<button class='btn btn-sm btn-primary' type = 'submit' value='Delete All Movies'>Delete All Movies</button>
					</form></td></tr></table>
				</div>
			</div>
		</div>";

if(isset($_GET['browse']) && $_GET['browse'] == 1){
	$browseCat = $_POST['dropCategory'];
	if ($browseCat == 'all'){
		createTable();
	}
	else{
		createBrowseTable($browseCat);
	}
}

//createTable();

echo "</body></html>";

?>