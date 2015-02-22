<?php

include_once __DIR__ . '/../src/ConfigLoader.php';

class MySqlFillIntTest extends PHPUnit_Framework_TestCase
{
    private $config;
    private $db;
    private $hostname;
    private $username;
    private $password;
    private $databaseName;
    private $tableName;


    // ******************************************************************************************************
    // ACTUAL TESTS
    // ******************************************************************************************************


    public function testItBasicallyWorks() { // TODO might want to do proper testing
        passthru("./mysqlfill {$this->databaseName} {$this->tableName}");
        $this->assertEquals(5, $this->countRows()); // TODO this will fail if an INSERT fails on a unique constraint for example
        // TODO also, this tests pretty much nothing other than the algorithm more or less works -_-
    }



    // ******************************************************************************************************
    // TEST FLOW ANNOYANCES
    // ******************************************************************************************************


    function setUp() {
        $this->loadConfigs();
        $this->initDatabase();

        $this->dropTable();
        $this->createTable(["name VARCHAR(50)", "date_of_birth DATETIME", "points BIGINT(11)"]);

        chdir(__DIR__ . "/..");
    }

    function tearDown() {
        $this->dropTable();
        $this->dropDatabase();
    }



    // ******************************************************************************************************
    // BOOTSTRAP ANNOYANCES
    // ******************************************************************************************************


    private function loadConfigs() {
        if(!$this->config) {
            $this->config = (new ConcreteConfigLoader(new MockConfigFromArguments()))->load();
            $this->hostname = $this->config["tests"]["hostname"];
            $this->username = $this->config["tests"]["username"];
            $this->password = $this->config["tests"]["password"];
            $this->databaseName = "mysqlfill_test_" . uniqid();
            $this->tableName = "mysqlfill_test_" . uniqid();
        }
    }

    private function initDatabase() {
        $dsn = "mysql:host={$this->hostname}";

        try {
            $this->db = new PDO($dsn, $this->username, $this->password);
            $this->dropDatabase();
            $this->createDatabase();
            $this->db = null;
        } catch (PDOException $e){
            die("Can't connect to DB. Error: [{$e->getMessage()}]"); // TODO in the future, let's have a better application flow
        }
    }



    // ******************************************************************************************************
    // MYSQL ANNOYANCES
    // ******************************************************************************************************


    private function createDatabase($databaseName = null) {
        $databaseName = $databaseName ?: $this->databaseName;
        return $this->runSql("CREATE DATABASE IF NOT EXISTS `{$databaseName}`;");
    }

    private function dropDatabase($databaseName = null) {
        $databaseName = $databaseName ?: $this->databaseName;
        return $this->runSql("DROP DATABASE IF EXISTS `{$databaseName}`;");
    }

    private function countRows($tableName = null, $countStrategy = "*") {
        $tableName = $tableName ?: $this->tableName;
        return $this->fetchColumn("SELECT COUNT({$countStrategy}) FROM `{$tableName}`;");
    }

    private function dropTable($tableName = null) {
        $tableName = $tableName ?: $this->tableName;
        return $this->runSql("DROP TABLE IF EXISTS `{$tableName}`;");
    }

    private function createTable($fields, $tableName = null) {
        $tableName = $tableName ?: $this->tableName;
        return $this->runSql("CREATE TABLE `{$tableName}` (" . implode(", ", $fields) . ");");
    }

    private function runSql($sql) {
        if(!$this->db)
            $this->getConnection();

        $query = $this->db->prepare($sql);
        return $query->execute();
    }

    private function fetchColumn($sql) {
        if(!$this->db)
            $this->getConnection();

        $query = $this->db->query($sql);
        return $query->fetchColumn();
    }

    public function getConnection()
    {
        $dsn = "mysql:host={$this->hostname};dbname={$this->databaseName}";

        try {
            $this->db = new PDO($dsn, $this->username, $this->password);
        } catch (PDOException $e){
            die("Can't connect to DB. Error: [{$e->getMessage()}]"); // TODO in the future, let's have a better application flow
        }
    }
}

class MockConfigFromArguments extends ConfigFromArguments {
    public function get($args) { return ["database_name" => "mock_database_name", "table_name" => "mock_table_name"]; }
}
