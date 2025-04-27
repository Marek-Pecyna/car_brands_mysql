<?php
// Strict requirement: declaration of definitive types
declare(strict_types=1);

// Show all errors with next two lines of code:
error_reporting(E_ALL);  // Report all errors
ini_set('display_errors', true);  // Show errors on webpage, change to false for web-facing servers as a security measure.

//////////////////////////////////////////////////////////////////////
require_once 'Logger.php';

class Model {

    private string $dbname;
    private string $host;
    private string $dbusername;
    private string $dbpassword;
    private ?string $logger = null;

    public function __construct(string $dbname='car_brands', 
                                string $host='127.0.0.1', 
                                string $dbusername='root', 
                                string $dbpassword='',
                                bool $logging=false) {
        $this->dbname = $dbname;
        $this->host = $host;
        $this->dbusername = $dbusername;
        $this->dbpassword = $dbpassword;
        $this->logger = ($logging) ? Logger::setLogger() : 'print_r';

        // Following two if-clauses serve only as a check for database connection control
        if (is_null($this->dbConnect())) {
            ($this->logger)('FEHLER der Datenbankverbindung');
            exit();
        }
        if (!$this->getData()) {
            ($this->logger)('FEHLER beim Daten auslesen');
            exit();
        }
        ($this->logger)("Object created.");
    }
  
    public function getData(): array {
        //Return array with all data from MySQL database
        $pdo = $this->dbConnect();
        $query = "SELECT (brands.id) AS id, (brands.name) AS brand, manufacturer.name AS company
        FROM brands INNER JOIN manufacturer ON brands.manufacturer_id = manufacturer.id;";
        return $this->sendQuery($pdo, $query, []);
    }

    public function addCar(string $brand, string $company): int|null {
        // Returns new generated brand.id if inseration was sucessful, otherwise "null"
        $already_known_manufacturer = true;
	    $manufacturer_id = $this->isManufacturerKnown($company);
	
        if (!$manufacturer_id) {
            // Manufacturer is unknown and is now being entered in the database
            $already_known_manufacturer = false;
            $manufacturer_id = $this->addManufacturer($company);
        }

        $id = $this->isBrandKnown($brand);

        if ($id and $already_known_manufacturer) { 
            return null;
         }

        $id = $this->addBrand($brand, $manufacturer_id);
        return $id;
    }

    public function editCar(int $id, string $brand, string $company) { 
        // Find manufacturer_id:
        $already_known_manufacturer = true;
        $manufacturer_id = $this->isManufacturerKnown($company);
    
        if (!$manufacturer_id) {
            // Manufacturer is unknown and is now being entered in the database
            $already_known_manufacturer = false;
            $manufacturer_id = $this->addManufacturer($company);
        }

        $brand_id = $this->isBrandKnown($brand);
        $already_known_brand = ($brand_id) ? true : false;

        if ($already_known_brand and $already_known_manufacturer) { 
            return null;
         }
        
        $pdo = $this->dbConnect();
        $query = "UPDATE brands SET name=:brand, manufacturer_id=:manufacturer_id WHERE brands.id=:id;";
        $result = $this->sendQuery($pdo, $query, [":brand"=>$brand, ":manufacturer_id"=>$manufacturer_id, ":id"=>$id]);
        return $id;
    }

    public function deleteCar(int $id) {
        $pdo = $this->dbConnect();
        $query = "DELETE FROM brands WHERE id=:id;";
        $result = $this->sendQuery($pdo, $query, [":id"=>$id]);
    }

    private function dbConnect(): PDO|null {
        $dsn = "mysql:host=$this->host;dbname=$this->dbname";
        try {
            $pdo = new PDO(
                $dsn, 
                $this->dbusername, 
                $this->dbpassword);
            
            // Error handling: if we get an error, we want throw an exception
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            ($this->logger)("Connection failed: '" . $e->getMessage() . "'");
            return null;
        }
        return $pdo;
    }

    private function sendQuery(PDO $pdo, string $query, array $named_parameter, 
                                int $fetch_mode=(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC)): array {
        // There is no check, if the named parameters match the query!
        $stmt = $pdo->prepare($query);
        try {
            $stmt->execute($named_parameter); 
            /* Alternative: 
            foreach ($named_parameter as $key => value) {
            $stmt->bindValue($key, $value);
            }
            stmt->execute();
            */
            $result = $stmt->fetchAll($fetch_mode);
        } catch (PDOException $e) {
            $stmt = null;
            $message = "Query failed: " . $query . "\n Error message: '" . $e->getMessage() . "'";
            ($this->logger)($message);
            exit(__CLASS__ . "->" . __FUNCTION__ . ": ". $message);
        }
        // echo $stmt->debugDumpParams();  // shows resulting params of finished SQL query
        $stmt = null;
        return $result;
    }

    private function isManufacturerKnown(string $manufacturer_name): int|null {
        // Search for already stored manufacturers
        // returns found id if known, else "null"
        $pdo = $this->dbConnect();
        $query = "SELECT (id) FROM manufacturer WHERE name=:name;";
        $result = $this->sendQuery($pdo, $query, [":name"=>$manufacturer_name]);
    
        $id = (empty($result)) ? null : array_key_first($result);
        ($this->logger)(($id) ? "true (id #$id)" : 'false');
        return $id;
    }
    
    private function isBrandKnown(string $brand_name): int|null {
        // Search for already stored brands (only unique brand names allowed)
        // returns found id if known, else "null"
        $pdo = $this->dbConnect();
        $query = "SELECT (id) FROM brands WHERE name=:name;";
        $result = $this->sendQuery($pdo, $query, [":name"=>$brand_name]);
    
        $id = (empty($result)) ? null : array_key_first($result);
        ($this->logger)(($id) ? "true (id #$id)" : 'false');
        return $id;
    }
    
    private function addManufacturer(string $new_manufacturer_name, 
                                      string $legal_form = "", 
                                      string $country_code = ""): int|null {
        // Insert new manufacturer into "manufacturer" table
        // Returns new generated id if inseration was sucessful, otherwise "null"
        $pdo = $this->dbConnect();
        $query = "INSERT INTO manufacturer (name, legal_form, country_code) VALUES (:name, :legal_form, :country_code);";
        $result = $this->sendQuery($pdo, $query, [':name'=>$new_manufacturer_name, ":legal_form"=>$legal_form, ":country_code"=>$country_code]);
        
        $query = "SELECT LAST_INSERT_ID();";
        $result = $this->sendQuery($pdo, $query, []);
    
        $id = (empty($result)) ? null : array_key_first($result);
        ($this->logger)("New manufacturer was registered to manufacturer table with the following ID: $id.");
        return $id;
    }
    
    private function addBrand(string $brand_name, int $manufacturer_id): int|null {
        $pdo = $this->dbConnect();
        $query = "INSERT INTO brands (name, manufacturer_id) VALUES (:brand_name, :manufacturer_id);";
        $result = $this->sendQuery($pdo, $query, [":brand_name"=>$brand_name, ":manufacturer_id"=>$manufacturer_id]);
    
        $query = "SELECT LAST_INSERT_ID();";
        $result = $this->sendQuery($pdo, $query, []);
    
        $id = (empty($result)) ? null : array_key_first($result);
        ($this->logger)("New brand was registered to brands table with the following ID: $id.");
        return $id;
    }

} 

// *******************************************************************
// * CLASS TEST ******************************************************
if (!debug_backtrace()) {  // analogous to __name__ == '__main__' in python
    // Code is only executed if *this* file is the entry point.
    require_once 'ClassInfo.php';
	$params = ['logging' => true];
    classInfo(Model::class, ...$params);
}
