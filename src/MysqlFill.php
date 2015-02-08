<?php
date_default_timezone_set("UTC");

include_once 'ConfigLoader.php';

class MySqlFill {
    private $tableStructure; // TODO in the future, we want to be able to fill many tables with one call
    private $configLoader;
    private $config;
    private $db;

    public function __construct(ConfigLoader $configLoader = null) { // TODO if we extract a class, let's receive it as a parameter here
        $this->configLoader = $configLoader ?: new ConcreteConfigLoader();
        $this->config = $this->configLoader->load();
        $this->db = $this->obtainDb();
        $this->tableStructure = $this->cleanUpTableStructure($this->obtainTableStructure());
    }

    public function run() { // TODO let's check if the table is empty first; if it's not don't do it! Or truncate the table first.
        for($i = 1; $i <= $this->config["rows_to_fill"]; $i++) { // TODO make this smarter; if an insert fails on a unique constraint, it will not fill all rows. Maybe COUNT(*) after a batch?
            $this->insertRow($this->createRandomRow());
        }
    }

    public function insertRow($row) {
        $fieldNames = implode(", ", array_keys($this->tableStructure));
        $questionMarks = implode(", ", array_fill(0, count($this->tableStructure), "?"));

        $sql = "INSERT INTO {$this->config["table_name"]} ({$fieldNames}) VALUES ({$questionMarks});";

        $query = $this->db->prepare($sql);
        $query->execute($row);
    }

    public function createRandomRow() { // TODO extract all randomness to a separate class so it can be mocked for tests
        $row = [];
        foreach($this->tableStructure as $column) {
            switch($column["type"]) {
                case "datetime":
                    $row[] = $this->getRandomDatetime();
                    break;
                case "varchar":
                    $row[] = $this->getRandomVarchar(); // TODO need the length here
                    break;
                case "bigint":
                case "int":
                    $row[] = $this->getRandomInt();
                    break;
                case "tinyint":
                case "smallint":
                case "mediumint":
                case "text":
                case "blob":
                case "enum":
                case "set":
                case "bit":
                default: // TODO might want to implement all these :)
                    die("Sorry bro! The data type [{$column["type"]}] is not yet supported! I can't fill your table.");
            }
        }
        return $row;
    }

    public function obtainDb() { // TODO this should be in a different class taken as parameter by the construct, so we can test
        $dsn = "mysql:host={$this->config["hostname"]};dbname={$this->config["database_name"]}";

        try {
            return new PDO($dsn, $this->config["username"], $this->config["password"]);
        } catch (PDOException $e){
            die("Can't connect to DB. Error: [{$e->getMessage()}]"); // TODO in the future, let's have a better application flow
        }
    }

    public function obtainTableStructure() {
        $sql = "
            SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_TYPE, COLUMN_KEY, EXTRA
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '{$this->config["database_name"]}'
            AND TABLE_NAME = '{$this->config["table_name"]}';
            "; // TODO possible SQL injection :( in the future, let's use interpolation for these 2 parameters

        $result = $this->db->query($sql);

        if($result === false) {
            die("Couldn't query INFORMATION_SCHEMA :( give me permissions!"); // TODO in the future, let's have a better application flow
        }

        return $result->fetchAll();
    }

    public function cleanUpTableStructure($tableStructure) {
        $newStructure = [];
        foreach ($tableStructure as $column) {
            if($column["EXTRA"] != "auto_increment") {
                $newStructure[$column["COLUMN_NAME"]] = [
                    "type" => $column["DATA_TYPE"],
                    "nullable" => $column["IS_NULLABLE"] === "YES" // TODO the reason for having this is picking null sometimes at random
                ];
            }
        }
        return $newStructure;
    }

    public function getRandomInt() {
        return rand(); // TODO is this the range a MySQL int accepts? What about nulls?
    }

    public function getRandomVarchar() {
        return uniqid(); // TODO need varchar length here (it's on COLUMN_TYPE) What about nulls?
    }

    public function getRandomDatetime() {
        return date("Y-m-d H:i:s", rand(0, time())); // TODO this is good for timestamp, not datetime (datetime range is greater) What about nulls?
    }
}

(new MySqlFill())->run(); // TODO take extra parameters, validate it contains something, validate table exists, etc
