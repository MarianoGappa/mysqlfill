<?php

class RowProducer {
    public $valueProducersForRow;

    public function __construct($valueProducersForRow) {
        // TODO validate valueProducers
        $this->valueProducersForRow = $valueProducersForRow;
    }

    public function produce() {
        $row = [];
        foreach($this->valueProducersForRow as $fieldName => $valueProducer) {
            $row[$fieldName] = $valueProducer->produce();
        }

        return $row;
    }
}

abstract class RowProducerFactory {
    abstract public function createFor($tableStructure);
}

class ConcreteRowProducerFactory {
    private $valueProducers;

    public function __construct($valueProducers = null) {
        // TODO validate non-empty array
        $this->valueProducers = $valueProducers ?: $this->defaultValueProducers();
    }

    public function createFor($tableStructure) {
        return new RowProducer($this->calculateValueProducersForRow($tableStructure));
    }

    private function calculateValueProducersForRow($tableStructure) {
        $producers = [];
        foreach ($tableStructure as $columnStructure) {
            foreach ($this->valueProducers as $valueProducer) {
                if($valueProducer::isFitGenerator($columnStructure)) {
                    $producers[$columnStructure->fieldName] = new $valueProducer($columnStructure);
                    break;
                }
            }
        }

        if(count($producers) !== count($tableStructure))
            throw new Exception("Could not find ValueProducers for all table columns.");

        return $producers;
    }

    private function defaultValueProducers() {
        return ["VarcharValueProducer", "DatetimeValueProducer", "IntValueProducer"];
    }
}
