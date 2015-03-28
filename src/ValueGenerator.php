<?php

abstract class ValueGenerator {
	public $columnStructure;

	public function __construct($columnStructure) {
		$this->columnStructure = $columnStructure;
	}
    abstract public function next();
    abstract public static function isFitGenerator($columnStructure);
}

class VarcharValueGenerator extends ValueGenerator {
    public function next() {
        return uniqid();
    }

    public static function isFitGenerator($columnStructure) {
        return $columnStructure->dataType == 'varchar';
    }
}

class DatetimeValueGenerator extends ValueGenerator {
    public function next() {
        return date('Y-m-d H:i:s', rand(0, time()));
    }

    public static function isFitGenerator($columnStructure) {
        return $columnStructure->dataType == 'datetime';
    }
}

class IntValueGenerator extends ValueGenerator {
    public function next() {
        return rand();
    }

    public static function isFitGenerator($columnStructure) {
        return in_array($columnStructure->dataType, ['bigint', 'int']);
    }
}

class EnumValueGenerator extends ValueGenerator {
	public function next() {
		return $this->columnStructure->values[array_rand($this->columnStructure->values)];
	}

	public static function isFitGenerator($columnStructure) {
		return $columnStructure->dataType == 'enum';
	}
}
