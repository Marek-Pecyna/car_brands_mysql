<?php
// Strict requirement: declaration of definitive types
declare(strict_types=1);

// Show all errors with next two lines of code:
error_reporting(E_ALL);  // Report all errors
ini_set('display_errors', true);  // Show errors on webpage, change to false for web-facing servers as a security measure.

//////////////////////////////////////////////////////////////////////
require_once 'Logger.php';

class Controller {

	private $model;
	private $view;
	private $logger = null;

	public function __construct($model, $view, bool $logging=false){
		$this->model = $model;
		$this->view = $view;
		$this->logger = ($logging) ? Logger::setLogger() : 'print_r';
        ($this->logger)("Object created.");
	}

	public function route(array $getParams, array $postParams) {
		// Route to model methods, if 'POST' request
		if (!empty($postParams)) {

			switch ($postParams['action']) {
				// case 'add':
				// 	$mainContent = $this->view->add();
				// 	break;
				case 'add_confirmed':
					$id = $this->addCar($postParams);
					header('Location: ./index.php?page=show&added=' . $id, true, 301);
					break;

				case 'edit':
					$id = intval($postParams['id']);
					$mainContent = $this->view->edit($id);
					break;
				case 'edit_confirmed':
					$id = $this->editCar($postParams);
					header('Location: ./index.php?page=show&edited=' . $id, true, 301);
					break;

				case 'delete':
					$id = intval($postParams['id']);
					$mainContent = $this->view->delete($id);
					break;
				case 'delete_confirmed':
					$id = $this->deleteCar($postParams);
					header('Location: ./index.php?page=show&deleted=' . $id, true, 301);
					break;
			}
		
		// Route to simple view methods, if 'get' request (show, add)
		} elseif (isset($getParams['page']) && !empty($getParams['page'])) {
			$page = method_exists($this->view, $getParams['page']) ? $getParams['page'] : 'welcome';
			$mainContent = $this->view->{$page}($getParams);
		} else {
			$mainContent = $this->view->welcome();
		}

		// Finally, main content is shown:
		echo $this->view->output($mainContent);
	}

	function addCar(array $postParams) {
		if(!isset($postParams['brand']) or !isset($postParams['company'])) { return null; }
		$brand = htmlentities(trim($postParams['brand']));
		$company = htmlentities(trim($postParams['company']));
		($this->logger)("Parsed Parameters: Brand: '$brand', Company: '$company'.");
	
		$id = $this->model->addCar($brand, $company);
		($this->logger)('Return: ' . ($id ? "#'$id' added." : "Nothing added, brand/company combination already present."));
		return $id;
	}

	function editCar(array $postParams) {
		if(!isset($postParams['brand']) or !isset($postParams['company']) or !isset($postParams['id'])) { return null; }
	
		$id = intval($postParams['id']);
		$brand = htmlentities(trim($postParams['brand']));
		$company = htmlentities(trim($postParams['company']));
		($this->logger)("Parsed Parameters: #'$id', Brand: '$brand', Company: '$company'.");
	
		$id = $this->model->editCar($id, $brand, $company);
		($this->logger)('Return: ' . ($id ? "#'$id' edited." : "Nothing edited, brand/company combination already present."));
		return $id;
	}
	
	function deleteCar(array $postParams) {
		if(!isset($postParams['id'])) { return null; }
		$id = intval($postParams['id']);
		$this->model->deleteCar($id);
		($this->logger)("#'$id' deleted.");
		return $id;
	}

}

// *******************************************************************
// * CLASS TEST ******************************************************
if (!debug_backtrace()) {  // analogous to __name__ == '__main__' in python
	// Code is only executed if *this* file is the entry point.
	require_once 'ClassInfo.php';
	$params = [null, null, true];
    classInfo(Controller::class, ...$params);
}
