<?php

class ColumnStructure {
    public $fieldName;
    public $dataType;
    public $isNullable;

    public function __construct($fieldName, $dataType, $isNullable) {
        $this->fieldName = $fieldName;
        $this->dataType = $dataType;
        $this->isNullable = $isNullable;
    }
}
