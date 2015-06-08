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
$addMovie = "http://" . $_SERVER['HTTP_HOST'] . $filePath . "/videoStore.php?addMovie=1";
$browseCategory = "http://" . $_SERVER['HTTP_HOST'] . $filePath . "/videoStore.php?browse=1";
$delAll = "http://" . $_SERVER['HTTP_HOST'] . $filePath . "/videoStore.php?delAll=1";

function getRentAdd($id){
	global $filePath;
	$rentAdd = "http://" . $_SERVER['HTTP_HOST'] . $filePath . "/videoStore.php?rentID=" . $id;
	return $rentAdd;
}

function getDelAdd($id){
	global $filePath;
	$delAdd = "http://" . $_SERVER['HTTP_HOST'] . $filePath . "/videoStore.php?deleteID=" . $id;
	return $delAdd;
}

$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "williasc-db", "qXQGn0fvpoKdYH26", "williasc-db");
if ($mysqli->connect_errno){
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

if(isset($_GET['addMovie']) && $_GET['addMovie'] == 1){
	$stmt = $mysqli->prepare("INSERT INTO videoDb(name,category,length) VALUES (?,?,?)");
	$stmt->bind_param('ssi', $_POST['name'], $_POST['category'], $_POST['length']);
	$stmt->execute();
	$stmt->close();
}

if(isset($_GET['delAll']) && $_GET['delAll'] == 1){
	$stmt = $mysqli->prepare("TRUNCATE TABLE videoDb");
	$stmt->execute();
	$stmt->close();
}

if(isset($_GET['rentID'])){
	$checkOut = $_GET['rentID'];
	if (!$stmt = $mysqli->prepare("SELECT rented FROM videoDb WHERE id = ?")){
	    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	$stmt->bind_param("i",$checkOut);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($available);
	$stmt->fetch();
	if ($available == 1){
		$newStmt = $mysqli->prepare("UPDATE videoDb SET rented = ? WHERE id = " . $checkOut);
		$newRent = 0;
		$newStmt->bind_param("i",$newRent);
		$newStmt->execute();
		$newStmt->close();
	}
	else{
		$newStmt = $mysqli->prepare("UPDATE videoDb SET rented = ? WHERE id = " . $checkOut);
		$newRent = 1;
		$newStmt->bind_param("i",$newRent);
		$newStmt->execute();
		$newStmt->close();
	}
	$stmt->close();
}

if(isset($_GET['deleteID'])){
	$delID = $_GET['deleteID'];
	if (!$stmt = $mysqli->prepare("DELETE FROM videoDb WHERE id = ?")){
	    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	$stmt->bind_param("i",$delID);
	$stmt->execute();
	$stmt->close();
}


function createTable(){
	global $mysqli;
	if (!($stmt = $mysqli->prepare("SELECT id, name, category, length, rented FROM videoDb"))) {
	    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	if (!$stmt->execute()) {
	    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	$out_id		= NULL;
	$out_name	= NULL;
	$out_cat	= NULL;
	$out_length	= NULL;
	$out_rented	= NULL;
	if (!$stmt->bind_result($out_id, $out_name, $out_cat, $out_length, $out_rented)) {
	    echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	}

	echo "<table class='table table-striped'>";
	echo "<thead><tr><th>ID</th><th>Title</th><th>Category</th><th>Length</th><th>Rent Status</th><th>Check Out/In</th><th>Delete</th></tr></thead>";
	while ($stmt->fetch()) {
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
	$stmt->close();
}

function createBrowseTable($browseCategory){
	global $mysqli;
	if (!($stmt = $mysqli->prepare("SELECT * FROM videoDb WHERE category = ?"))) {
	    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	$stmt->bind_param("s",$browseCategory);
	if (!$stmt->execute()) {
	    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	$out_id		= NULL;
	$out_name	= NULL;
	$out_cat	= NULL;
	$out_length	= NULL;
	$out_rented	= NULL;
	if (!$stmt->bind_result($out_id, $out_name, $out_cat, $out_length, $out_rented)) {
	    echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	}

	echo "<table class='table table-striped'>";
	echo "<thead><tr><th>ID</th><th>Title</th><th>Category</th><th>Length</th><th>Rent Status</th><th>Check Out/In</th><th>Delete</th></tr></thead>";
	while ($stmt->fetch()) {
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
	$stmt->close();
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
									$res = $mysqli->query("SELECT DISTINCT category FROM videoDb");
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