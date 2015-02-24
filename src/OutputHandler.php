<?php

interface OutputHandlerFactory {}

class ConcreteOutputHandlerFactory implements OutputHandlerFactory {
    public function forConfig($config, $tableStructure, TableStructureFetcher $tableStructureFetcher) {
        // TODO other types
        return new ConcreteOutputHandlerFromDatabase($config, $tableStructure, $tableStructureFetcher->db);
    }
}

abstract class OutputHandler {
    protected $tableName;

    public function __construct($config) {
        // TODO validate config
        $this->tableName = $config["table_name"];
    }

    public function before() {}
    public function after() {}
    abstract public function outputRow($row);
}

class ConcreteOutputHandlerFromDatabase extends OutputHandler {
    private $db;
    private $databaseName;
    private $tableStructure;

    public function __construct($config, $tableStructure, PDO $db) {
        parent::__construct($config);

        $this->databaseName = $config["database_name"];
        $this->tableStructure = $tableStructure;
        $this->db = $db;
    }

    public function outputRow($row) {
        $fieldNames = implode(", ", array_keys($this->tableStructure));
        $questionMarks = implode(", ", array_fill(0, count($this->tableStructure), "?"));

        $sql = "INSERT INTO {$this->tableName} ({$fieldNames}) VALUES ({$questionMarks});";

        $query = $this->db->prepare($sql);
        $query->execute(array_values($row));
    }
}
