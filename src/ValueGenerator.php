<?php

abstract class ValueGenerator {
	public $config;
	public $columnStructure;

	public function __construct($config, $columnStructure) {
		$this->config = $config;	
		$this->columnStructure = $this->columnStructure = $columnStructure;
	}
    abstract public function next();
    abstract public static function isFitGenerator($columnStructure);

    public function quote($string) {
        return "'{$string}'";
    }
}

class VarcharValueGenerator extends ValueGenerator {
    public function next() {
        return $this->quote(uniqid());
    }

    public static function isFitGenerator($columnStructure) {
        return $columnStructure->dataType == 'varchar';
    }
}

class DatetimeValueGenerator extends ValueGenerator {
    public function next() {
        return $this->quote(date('Y-m-d H:i:s', rand(0, time())));
    }

    public static function isFitGenerator($columnStructure) {
        return $columnStructure->dataType == 'datetime';
    }
}

class IntValueGenerator extends ValueGenerator {
    private static $ranges = [
        'tinyint'   => [ false => [ 'min' => -128,                 'max' => 127                 ], true => [ 'min' => 0, 'max' => 255 ] ],
        'smallint'  => [ false => [ 'min' => -32768,               'max' => 32767               ], true => [ 'min' => 0, 'max' => 65535 ] ],
        'int'       => [ false => [ 'min' => -2147483648,          'max' => 2147483647          ], true => [ 'min' => 0, 'max' => 4294967295 ] ],
        'mediumint' => [ false => [ 'min' => -8388608,             'max' => 8388607             ], true => [ 'min' => 0, 'max' => 16777215 ] ],
        'bigint'    => [ false => [ 'min' => -9223372036854775808, 'max' => 9223372036854775807 ], true => [ 'min' => 0, 'max' => 18446744073709551615 ] ]
    ];
	
    public function next() {
        $ranges = self::$ranges[$this->columnStructure->dataType][$this->columnStructure->unsigned];
        return rand($ranges['min'], $ranges['max']);
    }

    public static function isFitGenerator($columnStructure) {
        return in_array($columnStructure->dataType, array_keys(self::$ranges));
    }
}

class EnumValueGenerator extends ValueGenerator {
	public function next() {
	 	return $this->quote($this->columnStructure->values[array_rand($this->columnStructure->values)]);
	}

	public static function isFitGenerator($columnStructure) {
		return $columnStructure->dataType == 'enum';
	}
}
