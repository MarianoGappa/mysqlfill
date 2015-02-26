<?php

abstract class ValueGenerator {
    abstract public function __construct($columnStructure);
    abstract public function next();
    abstract public static function isFitGenerator($columnStructure);
}

class VarcharValueGenerator extends ValueGenerator {
    public function next() {
        return uniqid();
    }

    public function __construct($columnStructure) {}

    public static function isFitGenerator($columnStructure) {
        return $columnStructure->dataType == "varchar";
    }
}

class DatetimeValueGenerator extends ValueGenerator {
    public function next() {
        return date("Y-m-d H:i:s", rand(0, time()));
    }

    public function __construct($columnStructure) {}

    public static function isFitGenerator($columnStructure) {
        return $columnStructure->dataType == "datetime";
    }
}

class IntValueGenerator extends ValueGenerator {
    public function next() {
        return rand();
    }

    public function __construct($columnStructure) {}

    public static function isFitGenerator($columnStructure) {
        return in_array($columnStructure->dataType, ["bigint", "int"]);
    }
}
