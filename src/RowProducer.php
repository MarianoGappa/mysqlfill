<?php

class RowProducer {
    public $valueGeneratorsForRow;

    public function __construct($valueGeneratorsForRow) {
        // TODO validate valueGenerators
        $this->valueGeneratorsForRow = $valueGeneratorsForRow;
    }

    public function produce() {
        $row = [];
        foreach($this->valueGeneratorsForRow as $fieldName => $valueGenerator) {
            $row[$fieldName] = $valueGenerator->next();
        }

        return $row;
    }
}

abstract class RowProducerFactory {
    abstract public function forTableStructure($tableStructure);
}

class ConcreteRowProducerFactory {
    private $valueGenerators;

    public function __construct($valueGenerators = null) {
        // TODO validate non-empty array
        $this->valueGenerators = $valueGenerators ?: $this->defaultValueGenerators();
    }

    public function forTableStructure($tableStructure) {
        return new RowProducer($this->calculateValueGeneratorsForRow($tableStructure));
    }

    private function calculateValueGeneratorsForRow($tableStructure) {
        $generators = [];
        foreach ($tableStructure as $columnStructure) {
            foreach ($this->valueGenerators as $valueGenerator) {
                if($valueGenerator::isFitGenerator($columnStructure)) {
                    $generators[$columnStructure->fieldName] = new $valueGenerator($columnStructure);
                    break;
                }
            }
        }

        if(count($generators) !== count($tableStructure))
            throw new Exception('Could not find ValueGenerators for all table columns.');

        return $generators;
    }

    private function defaultValueGenerators() {
        return ['VarcharValueGenerator', 'DatetimeValueGenerator', 'IntValueGenerator', 'EnumValueGenerator'];
    }
}
