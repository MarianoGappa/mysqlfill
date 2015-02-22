<?php
date_default_timezone_set("UTC");

include_once __DIR__ . '/../src/RowProducer.php';
include_once __DIR__ . '/../src/ValueProducer.php';
include_once __DIR__ . '/../src/ColumnStructure.php';

class RowProducerTest extends PHPUnit_Framework_TestCase {
    public function testDefaultValueProducersWorkForCommonTypes() {
        $rowProducerFactory = new ConcreteRowProducerFactory();

        $rowProducer = $rowProducerFactory->createFor([new ColumnStructure("a_string", "varchar", false)]);
        $this->assertEquals("VarcharValueProducer", get_class($rowProducer->valueProducersForRow["a_string"]));

        $rowProducer = $rowProducerFactory->createFor([new ColumnStructure("an_int", "int", false)]);
        $this->assertEquals("IntValueProducer", get_class($rowProducer->valueProducersForRow["an_int"]));

        $rowProducer = $rowProducerFactory->createFor([new ColumnStructure("a_bigint", "bigint", false)]);
        $this->assertEquals("IntValueProducer", get_class($rowProducer->valueProducersForRow["a_bigint"]));

        $rowProducer = $rowProducerFactory->createFor([new ColumnStructure("a_date", "datetime", false)]);
        $this->assertEquals("DatetimeValueProducer", get_class($rowProducer->valueProducersForRow["a_date"]));
    }

    public function testDefaultRowProducerFactoryWorksForSeveralColumns() {
        $rowProducerFactory = new ConcreteRowProducerFactory();

        $rowProducer = $rowProducerFactory->createFor([
            new ColumnStructure("a_string", "varchar", false),
            new ColumnStructure("an_int", "int", false),
            new ColumnStructure("a_date", "datetime", false)
        ]);

        $this->assertCount(3, $rowProducer->valueProducersForRow);

        $row = $rowProducer->produce();

        $this->assertCount(3, $row);

        $this->assertInternalType("string", $row["a_string"]);
        $this->assertInternalType("int", $row["an_int"]);
        $this->assertInternalType("string", $row["a_date"]);
        $this->assertStringMatchesFormat("%d-%d-%d %d:%d:%d", $row["a_date"]);
    }
}
