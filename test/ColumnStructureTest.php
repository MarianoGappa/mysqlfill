<?php
date_default_timezone_set("UTC");

include_once __DIR__ . '/../src/ColumnStructure.php';

class ColumnStructureTest extends PHPUnit_Framework_TestCase {
    public function testColumnStructureDeterminesTypeProperly() {
        $columnStructure = new ColumnStructure("", "", false, "enum('weekly','fortnightly','monthly')");
	$this->assertEquals(['weekly','fortnightly','monthly'], $columnStructure->values);

        $columnStructure = new ColumnStructure("", "", false, "varchar(255)");
	$this->assertEquals(255, $columnStructure->length);

        $columnStructure = new ColumnStructure("", "", false, "char(80)");
	$this->assertEquals(80, $columnStructure->length);

        $columnStructure = new ColumnStructure("", "", false, "tinyint(1) unsigned");
	$this->assertEquals(true, $columnStructure->unsigned);
    }
}
