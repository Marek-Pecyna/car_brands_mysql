<?php
// Strict requirement: declaration of definitive types
declare(strict_types=1);

//Show all errors with next two lines of code:
error_reporting(E_ALL);
ini_set('display_errors', '1');

class model {

    public PDO|null $pdo;  //access to database as PHP data object
    private string $dbname;
    private string $host;
    private string $dbusername;
    private string $dbpassword;
    public array $cars;

    public function __construct(string $dbname='car_brands', 
                                string $host='127.0.0.1', 
                                string $dbusername='root', 
                                string $dbpassword='') {
        $this->dbname = $dbname;
        $this->host = $host;
        $this->dbusername = $dbusername;
        $this->dbpassword = $dbpassword;

        $this->pdo = $this->db_connect($this->dbname, );

        if (is_null($this->pdo)) {
            echo "<h1>FEHLER der Datenbankverbindung</h1>";
            exit();
        }
        if (!$this->get_data()) {
            echo "<h1>FEHLER beim Daten auslesen</h1>";
            exit();
        }
    }

    function db_connect(): PDO|null {
    
        $dsn = "mysql:host=$this->host;dbname=$this->dbname";
    
        try {
            $pdo = new PDO(
                $dsn, 
                $this->dbusername, 
                $this->dbpassword);
            
            // Error handling: if we get an error, we want throw an exception
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            return null;
        }
    
        return $pdo;
    }

    function get_data() {
        //Fill array from MySQL database

        // $query = "SELECT (brands.id) AS id, (brands.name) AS brand, CONCAT(manufacturer.name, ' ', manufacturer.legal_form) AS company FROM brands INNER JOIN manufacturer ON brands.manufacturer_id = manufacturer.id;";

        $query = "SELECT (brands.id) AS id, (brands.name) AS brand, manufacturer.name AS company FROM brands INNER JOIN manufacturer ON brands.manufacturer_id = manufacturer.id;";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
    
            $this->cars = $stmt->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error occured during query: " . $e->getMessage();
            return false;
        }
        return true;
    }

}


function module_test() {
    echo "<hr><h1>Module Test</h1>";
    echo "<h2>" . __FILE__ . "</h2><hr>";

    $model = new Model();

    echo '<h3>Attributes of $model:</h3>';
    echo "<pre>";
    print_r($model);
    echo "</pre>";
    echo "<hr>";

    echo '<h3>Methods of $model:</h3>';
    echo "<pre>";
    $class_methods = get_class_methods(get_class($model));
    foreach ($class_methods as $method_name) {
        echo "$method_name()\n";
    }
    echo "</pre>";
    // echo '<pre>' . print_r(get_defined_vars(), true) . '</pre>';  // Show all variables
}
    
    

// *******************************************************************
// * MODULE TEST *****************************************************
if (!debug_backtrace()) {  // analogous to __name__ == '__main__' in python
    module_test();         // Code is only executed if *this* file is the entry point.
}
