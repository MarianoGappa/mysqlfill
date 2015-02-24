<?php

interface TableStructureFetcherFactory {}

class ConcreteTableStructureFetcherFactory implements TableStructureFetcherFactory {
    public function forConfig($config) {
        // TODO other types
        return new ConcreteTableStructureFetcherFromDatabase($config);
    }
}

abstract class TableStructureFetcher {
    protected $tableName;

    public function __construct($config) {
        // TODO validate config
        $this->tableName = $config["table_name"];
    }

    abstract public function fetch();
}

class ConcreteTableStructureFetcherFromDatabase extends TableStructureFetcher {
    public $db;
    private $databaseName;

    public function __construct($config) {
        parent::__construct($config);

        $this->databaseName = $config["database_name"];
        $this->db = $this->obtainDb($config["hostname"], $config["database_name"], $config["username"], $config["password"]);
    }

    public function fetch() {
        return $this->parse($this->doFetch());
    }


    private function obtainDb($hostname, $databaseName, $username, $password) {
        $dsn = "mysql:host={$hostname};dbname={$databaseName}";

        try {
            return new PDO($dsn, $username, $password);
        } catch (PDOException $e){
            throw new Exception("Can't connect to DB. Error: [{$e->getMessage()}]"); // TODO in the future, let's have a better application flow
        }
    }

    private function doFetch() {
        $sql = "
            SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_TYPE, COLUMN_KEY, EXTRA
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '{$this->databaseName}'
            AND TABLE_NAME = '{$this->tableName}';
            "; // TODO possible SQL injection :( in the future, let's use interpolation for these 2 parameters

        $result = $this->db->query($sql);

        if($result === false) {
            throw new Exception("Couldn't query INFORMATION_SCHEMA :( give me permissions!"); // TODO in the future, let's have a better application flow
        }

        $result = $result->fetchAll();

        if(!$result) {
            throw new Exception("Table structure for table [{$this->tableName}] on database [{$this->databaseName}] could not be obtained or is empty :("); // TODO in the future, let's have a better application flow
        }

        return $result;
    }

    private function parse($tableStructure) {
        $newStructure = [];
        foreach ($tableStructure as $column) {
            if($column["EXTRA"] != "auto_increment") {
                $newStructure[$column["COLUMN_NAME"]] = new ColumnStructure($column["COLUMN_NAME"], $column["DATA_TYPE"], $column["IS_NULLABLE"] === "YES");
            }
        }
        return $newStructure;
    }
}
