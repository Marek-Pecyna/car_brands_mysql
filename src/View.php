<?php
// Strict requirement: declaration of definitive types
declare(strict_types=1);

// Show all errors with next two lines of code:
error_reporting(E_ALL);  // Report all errors
ini_set('display_errors', true);  // Show errors on webpage, change to false for web-facing servers as a security measure.

//////////////////////////////////////////////////////////////////////
require_once 'Logger.php';

class View {

    private $model;
    private $controller;
	private ?string $logger = null;

    public function __construct($model, bool $logging=false) {
        $this->model = $model;
        $this->controller = null;
		$this->logger = ($logging) ? Logger::setLogger() : 'print_r';
        ($this->logger)("Object created.");
    }

    public function output($mainContent) {
        include('./html_templates/main_template.html');
    }

    public function show(array $getParams) {
		ob_start();  // start output buffer for collecting all content for main template

		if(isset($getParams['added'])) {
			if (empty($getParams['added'])) {
				echo '<p>Kein neuer Eintrag: Automarke/Hersteller-Kombination bereits bekannt.</p>';
			} else {
				echo "<p>Automarke ID# <b>'" . $getParams['added'] . "'</b> wurde hinzugefügt.</p>";
			}
		}

		if(isset($getParams['edited'])) {
			if (empty($getParams['edited'])) {
				echo '<p>Keine Änderung: Automarke/Hersteller-Kombination bereits bekannt.</p>';
			} else {
				echo "<p>Automarke ID# <b>'" . $getParams['edited'] . "'</b> wurde geändert.</p>";
			}
		}

		if(isset($getParams['deleted']) and !empty($getParams['deleted'])) {
			echo "<p>Automarke ID# <b>'" . $getParams['deleted'] . "'</b> wurde gelöscht.</p>";
		}

		echo '<h2>Auflistung der Automarken</h2>';
		echo '<hr>';
		echo '<p>Ein Überblick über die gespeicherten <b>Automarken</b></p>';
		echo "<div class='scroll_box'>";

		$cars = $this->model->getData();
		foreach ($cars as $id => $row) {
			$brand = $row['brand'];
			$company = $row['company'];
			include('./html_templates/show_frame.html');
		}
		echo '</div>';
		return ob_get_clean();
	}

	public function add() {
		ob_start();
		include('./html_templates/add_frame.html');
		return ob_get_clean();
	}

	public function edit(int $id) {
		$cars = $this->model->getData();
		$brand = $cars[$id]['brand'];
		$company = $cars[$id]['company'];
		ob_start();
		include('./html_templates/edit_frame.html');
		return ob_get_clean();
	}

	public function delete(int $id) {		
		$cars = $this->model->getData();
		$brand = $cars[$id]['brand'];
		$company = $cars[$id]['company'];
		ob_start();
		include('./html_templates/delete_frame.html');
		return ob_get_clean();
	}

	public function welcome() {
		ob_start();
		include('./html_templates/welcome.html');
		return ob_get_clean();
	}

}  

// *******************************************************************
// * CLASS TEST ******************************************************
if (!debug_backtrace()) {  // analogous to __name__ == '__main__' in python
	// Code is only executed if *this* file is the entry point.
	require_once 'ClassInfo.php';
	$params = ['model' => null, 'logging' => true];
	classInfo(View::class, ...$params);
}
