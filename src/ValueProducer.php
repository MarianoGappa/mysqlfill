<?php

abstract class ValueProducer {
    abstract public function __construct($columnStructure);
    abstract public function produce();
    abstract public static function isFitGenerator($columnStructure);
}

class VarcharValueProducer extends ValueProducer {
    public function produce() {
        return uniqid();
    }

    public function __construct($columnStructure) {}

    public static function isFitGenerator($columnStructure) {
        return $columnStructure->dataType == "varchar";
    }
}

class DatetimeValueProducer extends ValueProducer {
    public function produce() {
        return date("Y-m-d H:i:s", rand(0, time()));
    }

    public function __construct($columnStructure) {}

    public static function isFitGenerator($columnStructure) {
        return $columnStructure->dataType == "datetime";
    }
}

class IntValueProducer extends ValueProducer {
    public function produce() {
        return rand();
    }

    public function __construct($columnStructure) {}

    public static function isFitGenerator($columnStructure) {
        return in_array($columnStructure->dataType, ["bigint", "int"]);
    }
}
