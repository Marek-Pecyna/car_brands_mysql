<?php
// strict requirement: declaration of definitive types
declare(strict_types=1);

//Show all errors with next two lines of code:
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Getting the POST request superglobal
$the_request = ($_SERVER["REQUEST_METHOD"] == 'POST') ? $_POST : "";

//Skript wurde irregulär aufgerufen
// Rückkehr zur Startseite
if (empty($the_request)) {
	header("Location: ../index.php");
	exit();
}

//Get data
require_once "model.php";
$model = new Model();

//Methode anhand von POST-Parameter ausgewählt
switch ($the_request['action']) {
	case "add":
		$id = add_car($model);
		header('Location: ../index.php?page=show&added=' . $id, true, 301);
		break;
	case "edit":
		$id = edit_car($model);
		header('Location: ../index.php?page=show&edited=' . $id, true, 301);
		break;
	case "delete":
		$id = delete_car($model);
		header('Location: ../index.php?page=show&deleted=' . $id, true, 301);
		break;
}

//Ende von Formhandler, falls noch nicht vorher beendet und Rückkehr zur Hauptseite
exit();

/* Funktionsdefinitionen ******************************************************************** */
function send_query($model, string $query, array $named_parameter, $fetch_mode=(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)) {
	// There is no check, if the parameters match the query!
	$stmt = $model->pdo->prepare($query);
	echo "<hr>ANFANG " . __FUNCTION__ . "():<br>";
	try {
		foreach ($named_parameter as $key => $value) {
			echo "$key: $value<br>";
			$stmt->bindParam($key, $value);
		}
		$stmt->execute();
		$result = $stmt->fetchAll($fetch_mode);
	} catch (PDOException $e) {
		$stmt = null;
		exit(__FUNCTION__ . "(): Query failed: " . $query . "\n<br> Error message: '" . $e->getMessage() . "'");
	}
	echo "<pre>";
	echo $stmt->debugDumpParams();
	echo "</pre>";
	echo "<br>ENDE " . __FUNCTION__ . "()<br><br>";
	$stmt = null;
	return $result;
}

function is_manufacturer_known($model, $manufacturer_name) {
	// Search for already stored manufacturers
	// returns found id if known, else "null"
	$query = "SELECT (id) FROM manufacturer WHERE name=:name;";
	$result = send_query($model, $query, [":name"=>$manufacturer_name]);

	$id = (empty($result)) ? null : array_key_first($result);
	echo __FUNCTION__ . ":'$id'<br>";
	return $id;
}

function is_brand_known($model, $brand_name) {
	// Search for already stored brands (only unique brand names allowed)
	// returns found id if known, else "null"
	$query = "SELECT (id) FROM brands WHERE name=:name;";
	$result = send_query($model, $query, [":name"=>$brand_name]);

	$id = (empty($result)) ? null : array_key_first($result);
	echo __FUNCTION__ . ":'$id'<br>";
	return $id;
}

function add_manufacturer($model, $new_manufacturer_name, $legal_form = "", $country_code = "") {
	// Insert new manufacturer into "manufacturer" table
	// returns new generated id if inseration was sucessful, otherwise "null"
	$query = "INSERT INTO manufacturer (name, legal_form, country_code) VALUES (:name, :legal_form, :country_code);";
	$result = send_query($model, $query, [':name'=>$new_manufacturer_name, ":legal_form"=>$legal_form, ":country_code"=>$country_code]);
	var_export($result);
	echo "<hr>";
	
	$query = "SELECT LAST_INSERT_ID();";
	$result = send_query($model, $query, []);
	var_export($result);
	echo "<hr>";

	$id = (empty($result)) ? null : array_key_first($result);
	echo __FUNCTION__ . ":'$id'<br>";
	return $id;
}

function add_brand($model, $brand_name, $manufacturer_id) {
	$query = "INSERT INTO brands (name, manufacturer_id) VALUES (:brand_name, :manufacturer_id);";
	$result = send_query($model, $query, [":brand_name"=>$brand_name, ":manufacturer_id"=>$manufacturer_id]);
}

function add_car($model) {

	if(!isset($_POST['brand']) or !isset($_POST['company'])) { return null; }

	$brand = htmlentities(trim($_POST['brand']));
	$company = htmlentities(trim($_POST['company']));

	$already_known_manufacturer = true;
	$manufacturer_id = is_manufacturer_known($model, $company);
	
	if (!$manufacturer_id) {
		// Hersteller ist noch unbekannt und wird jetzt in Datenbank eingetragen!
		echo $company . ": Dies ist ein noch unbekannter Hersteller!<br>";
		$already_known_manufacturer = false;
		$manufacturer_id = add_manufacturer($model, $company);
		if ($manufacturer_id) {
			echo "<br>Neuer Hersteller wurde eingetragen mit der folgenden ID: '" . $manufacturer_id ."'<br>";
		} else {
			echo "<br>Auslesen der letzten ID hat nicht funktioniert.<br>";
		}
	}

	$id = is_brand_known($model, $brand);

	if ($id and $already_known_manufacturer) {
		return null;
	}
	add_brand($model, $brand, $manufacturer_id);
	die();
	return $id;
}

function edit_car($model) {
	if(!isset($_POST['brand']) or !isset($_POST['company']) or !isset($_POST['id'])) { return null; }

	$id = $_POST['id'];
	$brand = htmlentities(trim($_POST['brand']));
	$company = htmlentities(trim($_POST['company']));
	echo "Ermittelt zum Ändern:<br>Marken-ID: $id<br>Markenname: $brand<br>Hersteller: $company<br>";

	// Find manufacturer_id:
	$manufacturer_id = is_manufacturer_known($model, $company);

	if (!$manufacturer_id) {
		// Hersteller ist noch unbekannt und wird jetzt in Datenbank eingetragen!
		echo $company . ": Dies ist ein noch unbekannter Hersteller!<br>";
		$manufacturer_id = add_manufacturer($model, $company);

		if ($manufacturer_id) {
			echo "<br>Neuer Hersteller wurde eingetragen mit der folgenden ID: '" . $manufacturer_id ."'<br>";
		} else {
			echo "<br>Auslesen der letzten ID hat nicht funktioniert.<br>";
		}
	}

	$query = "UPDATE brands SET name=:brand, manufacturer_id=:manufacturer_id WHERE brands.id=:id;";
	echo "Markenname: $brand";
	$result = send_query($model, $query, [":brand"=>$brand, ":manufacturer_id"=>$manufacturer_id, ":id"=>$id]);
	
	die();
	return $id;
}

function delete_car($model) {
	
	// If 'id' not set, return
	if(!isset($_POST['id'])) { return null; }

	$id = $_POST['id'];
	$query = "DELETE FROM brands WHERE id=:id;";
	$result = send_query($model, $query, [":id"=>$id]);

	return $id;
}		

