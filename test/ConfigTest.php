<?php

include_once '../src/ConfigLoader.php';

// Note: create the `test_mysqlfill` database before running the tests
// TODO any way we can automate this?

class MySqlFillTest extends PHPUnit_Framework_TestCase
{
    public function testConfigFromArguments() {
        $configFromArguments = new ConcreteConfigFromArguments();

        $actual = $configFromArguments->get(["mysqlfill", "test_table"]);
        $expected = ["table_name" => "test_table"];

        $this->assertEquals($actual, $expected);

        $actual = $configFromArguments->get(["mysqlfill", "test_database", "test_table"]);
        $expected = ["database_name" => "test_database", "table_name" => "test_table"];

        $this->assertEquals($actual, $expected);

        $actual = $configFromArguments->get(["mysqlfill", "-h", "localhost"]);
        $expected = ["hostname" => "localhost"];

        $this->assertEquals($actual, $expected);

        $actual = $configFromArguments->get(["mysqlfill", "-h", "localhost", "-u", "root"]);
        $expected = ["hostname" => "localhost", "username" => "root"];

        $this->assertEquals($actual, $expected);

        $actual = $configFromArguments->get(["mysqlfill", "-u", "root", "-p", "1234", "-h", "localhost"]);
        $expected = ["hostname" => "localhost", "username" => "root", "password" => "1234"];

        $this->assertEquals($actual, $expected);

        $actual = $configFromArguments->get(["mysqlfill", "-u", "root", "-p", "1234", "-h", "localhost", "test_table"]);
        $expected = ["hostname" => "localhost", "username" => "root", "password" => "1234", "table_name" => "test_table"];

        $this->assertEquals($actual, $expected);

        $actual = $configFromArguments->get(["mysqlfill", "-u", "root", "-p", "1234", "-h", "localhost", "test_database", "test_table"]);
        $expected = ["hostname" => "localhost", "username" => "root", "password" => "1234", "database_name" => "test_database", "table_name" => "test_table"];

        $this->assertEquals($actual, $expected);
    }

    public function testConfigFromFile() {
        $uniqid = uniqid();
        file_put_contents($uniqid, "<?php
            return
            [
                'table_name' => 'test_table',
                'database_name' => 'test_database'
            ];
        ");

        $configFromFile = new ConcreteConfigFromFile();

        $actual = $configFromFile->get($uniqid);
        $expected = ["table_name" => "test_table", "database_name" => "test_database"];

        unlink($uniqid);

        $this->assertEquals($actual, $expected);
    }

    public function testConfigFallback() {
        global $configLoaderDir; // N.B. any other way to get absolute path from different file?

        $uniqid = uniqid();
        file_put_contents($uniqid, "<?php
            return
            [
                'rows_to_fill' => 100,
                'database_name' => 'test_database'
            ];
        ");

        $args = ["mysqlfill", "-h", "127.0.0.1", "another_test_database", "test_table"];

        $configLoader = new ConcreteConfigLoader(null, null, null, new MockConfigValidator());

        $expected = [
            "table_name" => "test_table",
            "database_name" => "another_test_database",
            "hostname" => "127.0.0.1",
            "username" => "root",
            "password" => "",
            "on_table_not_empty" => "abort",
            "utf8" => false,
            "predictive" => true,
            "config_path" => $configLoaderDir . "/../mysqlfill.conf",
            "rows_to_fill" => 100
        ];

        $actual = $configLoader->load($args, $uniqid);

        unlink($uniqid);

        $this->assertEquals($actual, $expected);
    }
}

class MockConfigValidator extends ConfigValidator {
    public function validate($config) {}
}