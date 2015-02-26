<?php
date_default_timezone_set("UTC");

include_once __DIR__ . '/../src/RowProducer.php';
include_once __DIR__ . '/../src/ValueGenerator.php';
include_once __DIR__ . '/../src/ColumnStructure.php';

class RowProducerTest extends PHPUnit_Framework_TestCase {
    public function testDefaultValueProducersWorkForCommonTypes() {
        $rowProducerFactory = new ConcreteRowProducerFactory();

        $rowProducer = $rowProducerFactory->forTableStructure([new ColumnStructure("a_string", "varchar", false)]);
        $this->assertEquals("VarcharValueGenerator", get_class($rowProducer->valueGeneratorsForRow["a_string"]));

        $rowProducer = $rowProducerFactory->forTableStructure([new ColumnStructure("an_int", "int", false)]);
        $this->assertEquals("IntValueGenerator", get_class($rowProducer->valueGeneratorsForRow["an_int"]));

        $rowProducer = $rowProducerFactory->forTableStructure([new ColumnStructure("a_bigint", "bigint", false)]);
        $this->assertEquals("IntValueGenerator", get_class($rowProducer->valueGeneratorsForRow["a_bigint"]));

        $rowProducer = $rowProducerFactory->forTableStructure([new ColumnStructure("a_date", "datetime", false)]);
        $this->assertEquals("DatetimeValueGenerator", get_class($rowProducer->valueGeneratorsForRow["a_date"]));
    }

    public function testDefaultRowProducerFactoryWorksForSeveralColumns() {
        $rowProducerFactory = new ConcreteRowProducerFactory();

        $rowProducer = $rowProducerFactory->forTableStructure([
            new ColumnStructure("a_string", "varchar", false),
            new ColumnStructure("an_int", "int", false),
            new ColumnStructure("a_date", "datetime", false)
        ]);

        $this->assertCount(3, $rowProducer->valueGeneratorsForRow);

        $row = $rowProducer->produce();

        $this->assertCount(3, $row);

        $this->assertInternalType("string", $row["a_string"]);
        $this->assertInternalType("int", $row["an_int"]);
        $this->assertInternalType("string", $row["a_date"]);
        $this->assertStringMatchesFormat("%d-%d-%d %d:%d:%d", $row["a_date"]);
    }
}
