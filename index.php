<?php
// strict requirement: declaration of definitive types
declare(strict_types=1);

//Show all errors with next two lines of code:
error_reporting(E_ALL);
ini_set('display_errors', true);

//////////////////////////////////////////////////////////////////////
// start output buffer for collecting all content for main template
ob_start();

//Get data
require_once "./data/model.php";
$model = new Model();

//Define functions
function show_all_cars($model) {
	echo '<h2>Auflistung der Automarken</h2>';
	echo '<hr>';
	echo "<p>Ein Überblick über die gespeicherten <b>Automarken</b></p>";
	echo "<div class='scroll_box'>";

	foreach ($model as $id => $row) {
		$brand = $row['brand'];
		$company = $row['company'];
		include('./templates/show_frame.html');
	}
	echo "</div>";
}

function delete_car($model) {		
	if(isset($_POST['delete'])) {
		$id = $_POST['delete'];
		$brand = $model[$id]['brand'];
		$company = $model[$id]['company'];
		include('./templates/delete_frame.html');
		return 0;
	}
	return 1;
}

function edit_car($model) {
	if(isset($_POST['edit'])) {
		$id = $_POST['edit'];
		$brand = $model[$id]['brand'];
		$company = $model[$id]['company'];
		include('./templates/edit_frame.html');
		return 0;
	}
	return 1;
}

function welcome() {
	include('./templates/welcome.html');
}

//$_GET liefert URL-Parameter zurück
// Wenn nicht gesetzt, dann Willkomensseite anzeigen
if(isset($_GET['page'])) {
	$choose = $_GET['page'];
	switch ($choose) {
		case "show":
			if(isset($_GET['added']) and !empty($_GET['added'])) {
				echo "<p>Automarke ID# <b>'" . $_GET['added'] . "'</b> wurde hinzugefügt.</p>";
			}
			if(isset($_GET['deleted']) and !empty($_GET['deleted'])) {
				echo "<p>Automarke ID# <b>'" . $_GET['deleted'] . "'</b> wurde gelöscht.</p>";
			}
			if(isset($_GET['edited']) and !empty($_GET['edited'])) {
				echo "<p>Automarke ID# <b>'" . $_GET['edited'] . "'</b> wurde geändert.</p>";
			}
			show_all_cars($model->cars);
			break;
		case "add":
			include("./templates/add_frame.html");		
			break;
		case "delete":
			$status = delete_car($model->cars);
			if ($status == 1) {welcome();}
			break;
		case "edit":
			$status = edit_car($model->cars);
			if ($status == 1) {welcome();}
			break;
		default:
			welcome();
	}

} else {
	welcome();
}

$mainContent = ob_get_clean();
include('./templates/main_template.html');

