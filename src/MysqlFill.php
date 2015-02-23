<?php
date_default_timezone_set("UTC");

include_once __DIR__ . '/ConfigLoader.php';
include_once __DIR__ . '/RowProducer.php';
include_once __DIR__ . '/ValueProducer.php';
include_once __DIR__ . '/ColumnStructure.php';
include_once __DIR__ . '/TableStructureFetcher.php';

class MySqlFill {
    private $db;

    private $configLoader;
    private $config;

    private $rowProducerFactory;
    private $rowProducer;

    private $tableStructureFetcherFactory;
    private $tableStructureFetcher;
    private $tableStructure; // TODO in the future, we want to be able to fill many tables with one call

    public function __construct(
        ConfigLoader $configLoader = null,
        RowProducerFactory $rowProducerFactory = null,
        TableStructureFetcherFactory $tableStructureFetcherFactory = null
    ) {
        $this->configLoader = $configLoader ?: new ConcreteConfigLoader();
        $this->rowProducerFactory = $rowProducerFactory ?: new ConcreteRowProducerFactory();
        $this->tableStructureFetcherFactory = $tableStructureFetcherFactory ?: new ConcreteTableStructureFetcherFactory();

        $this->config = $this->configLoader->load();
        $this->db = $this->obtainDb();
        $this->tableStructureFetcher = $this->tableStructureFetcherFactory->forDatabaseTable($this->config, $this->db); // TODO instead of forDatabaseTable use "forConfig", to allow different types of fetchers (e.g. from sql file)
        $this->tableStructure = $this->tableStructureFetcher->fetch();
        $this->rowProducer = $this->rowProducerFactory->forTableStructure($this->tableStructure);
    }

    public function run() { // TODO let's check if the table is empty first; if it's not don't do it! Or truncate the table first.
        for($i = 1; $i <= $this->config["rows_to_fill"]; $i++) { // TODO make this smarter; if an insert fails on a unique constraint, it will not fill all rows. Maybe COUNT(*) after a batch?
            $this->insertRow($this->rowProducer->produce());
        }
    }

    private function insertRow($row) {
        $fieldNames = implode(", ", array_keys($this->tableStructure));
        $questionMarks = implode(", ", array_fill(0, count($this->tableStructure), "?"));

        $sql = "INSERT INTO {$this->config["table_name"]} ({$fieldNames}) VALUES ({$questionMarks});";

        $query = $this->db->prepare($sql);
        $query->execute(array_values($row));
    }

    private function obtainDb() { // TODO this should be in a different class taken as parameter by the construct, so we can test
        $dsn = "mysql:host={$this->config["hostname"]};dbname={$this->config["database_name"]}";

        try {
            return new PDO($dsn, $this->config["username"], $this->config["password"]);
        } catch (PDOException $e){
            die("Can't connect to DB. Error: [{$e->getMessage()}]"); // TODO in the future, let's have a better application flow
        }
    }


}

(new MySqlFill())->run(); // TODO take extra parameters, validate it contains something, validate table exists, etc
