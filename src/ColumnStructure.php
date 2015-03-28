<?php

class ColumnStructure {
    public $fieldName;
    public $dataType;
    public $isNullable;
    public $unsigned;
    public $length = 0;
    public $values = [];

    public function __construct($fieldName, $dataType, $isNullable, $columnType) {
        $this->fieldName = $fieldName;
        $this->dataType = $dataType;
	$this->isNullable = $isNullable;

	$this->unsigned = strpos($columnType, 'unsigned') !== false;
	if(preg_match('/char\((\d+)\)/', $columnType, $matches))
		$this->length = $matches[1];
	if(preg_match_all("/'([^']+?)'/", $columnType, $matches))
		$this->values = $matches[1];
    }
}
