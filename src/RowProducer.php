<?php

class RowProducer {
	public $config;
	public $valueGeneratorsForRow;

    public function __construct($config, $valueGeneratorsForRow) {
		$this->config = $config;
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

    public function forTableStructure($config, $tableStructure) {
        return new RowProducer($config, $this->calculateValueGeneratorsForRow($config, $tableStructure));
    }

    private function calculateValueGeneratorsForRow($config, $tableStructure) {
        $generators = [];
        foreach ($tableStructure as $columnStructure) {
            foreach ($this->valueGenerators as $valueGenerator) {
                if($valueGenerator::isFitGenerator($columnStructure)) {
                    $generators[$columnStructure->fieldName] = new $valueGenerator($config, $columnStructure);
                    break;
                }
            }
        }

        if(count($generators) !== count($tableStructure))
            throw new Exception('Could not find ValueGenerators for all table columns.');

        return $generators;
    }

    private function defaultValueGenerators() {
        return [
            'VarcharValueGenerator', 
            'DatetimeValueGenerator', 
            'IntValueGenerator', 
            'EnumValueGenerator', 
            'DecimalValueGenerator', 
            'FloatValueGenerator',
            'TimestampValueGenerator',
            'DateValueGenerator'
        ];
    }
}
