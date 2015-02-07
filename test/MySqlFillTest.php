<?php
// Note: create the `test_mysqlfill` database before running the tests
// TODO any way we can automate this?
// TODO Trying to make a new source of power.

class MySqlFillTest extends PHPUnit_Framework_TestCase
{
    private $db;
    private $tableName = "test_table";

    function setUp() {
        $this->getConnection();

        $sql = "DROP TABLE IF EXISTS `{$this->tableName}`;";

        $query = $this->db->prepare($sql);
        $query->execute();

        $sql .= "CREATE TABLE `{$this->tableName}` (name VARCHAR(50), date_of_birth DATETIME, points BIGINT(11));";

        $query = $this->db->prepare($sql);
        $query->execute();

        chdir("../src");
    }

    function tearDown() {
        $sql = "DROP TABLE IF EXISTS `{$this->tableName}`;";

        $query = $this->db->prepare($sql);
        $query->execute();
    }

    public function getRowCount() {
        $sql = "SELECT COUNT(*) FROM `{$this->tableName}`;";

        $query = $this->db->query($sql);
        return $query->fetchColumn();
    }

    public function getConnection()
    {
        $host ='localhost'; // TODO take dbconnection from some config
        $dbName = 'test_mysqlfill';
        $username = 'root';
        $password = '';
        $dsn = "mysql:host=$host;dbname=$dbName";

        try {
            $this->db = new PDO($dsn, $username, $password);
        } catch (PDOException $e){
            die("Can't connect to DB. Error: [{$e->getMessage()}]"); // TODO in the future, let's have a better application flow
        }
    }

    public function testItBasicallyWorks() { // TODO might want to do proper testing
        `php mysqlfill test_table`;
        $this->assertEquals(5, $this->getRowCount()); // TODO this will fail if an INSERT fails on a unique constraint for example
        // TODO also, this tests pretty much nothing other than the algorithm more or less works -_-
    }
}
