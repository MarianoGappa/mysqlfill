<?php
date_default_timezone_set("UTC");

include_once __DIR__ . '/ConfigLoader.php';
include_once __DIR__ . '/RowProducer.php';
include_once __DIR__ . '/ValueProducer.php';
include_once __DIR__ . '/ColumnStructure.php';

class MySqlFill {
    private $tableStructure; // TODO in the future, we want to be able to fill many tables with one call
    private $configLoader;
    private $config;
    private $db;
    private $rowProducerFactory;
    private $rowProducer;

    public function __construct(ConfigLoader $configLoader = null, RowProducerFactory $rowProducerFactory = null) {
        $this->configLoader = $configLoader ?: new ConcreteConfigLoader();
        $this->rowProducerFactory = $rowProducerFactory ?: new ConcreteRowProducerFactory();

        $this->config = $this->configLoader->load();
        $this->db = $this->obtainDb();
        $this->tableStructure = $this->cleanUpTableStructure($this->obtainTableStructure());
        $this->rowProducer = $this->rowProducerFactory->createFor($this->tableStructure);
    }

    public function run() { // TODO let's check if the table is empty first; if it's not don't do it! Or truncate the table first.
        for($i = 1; $i <= $this->config["rows_to_fill"]; $i++) { // TODO make this smarter; if an insert fails on a unique constraint, it will not fill all rows. Maybe COUNT(*) after a batch?
            $this->insertRow($this->rowProducer->produce());
        }
    }

    public function insertRow($row) {
        $fieldNames = implode(", ", array_keys($this->tableStructure));
        $questionMarks = implode(", ", array_fill(0, count($this->tableStructure), "?"));

        $sql = "INSERT INTO {$this->config["table_name"]} ({$fieldNames}) VALUES ({$questionMarks});";

        $query = $this->db->prepare($sql);
        $query->execute(array_values($row));
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

        $result = $result->fetchAll();

        if(!$result) {
            die("Table structure for table [{$this->config["table_name"]}] on database [{$this->config["database_name"]}] could not be obtained or is empty :("); // TODO in the future, let's have a better application flow
        }

        return $result;
    }

    public function cleanUpTableStructure($tableStructure) {
        $newStructure = [];
        foreach ($tableStructure as $column) {
            if($column["EXTRA"] != "auto_increment") {
                $newStructure[$column["COLUMN_NAME"]] = new ColumnStructure($column["COLUMN_NAME"], $column["DATA_TYPE"], $column["IS_NULLABLE"] === "YES");
            }
        }
        return $newStructure;
    }
}

(new MySqlFill())->run(); // TODO take extra parameters, validate it contains something, validate table exists, etc
