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

class DateValueGenerator extends ValueGenerator {
    public function next() {
        return $this->quote(date('Y-m-d', rand(0, time())));
    }

    public static function isFitGenerator($columnStructure) {
        return $columnStructure->dataType == 'date';
    }
}

class TimestampValueGenerator extends ValueGenerator {
    public function next() {
        return $this->quote(date('Y-m-d H:i:s', rand(0, time())));
    }

    public static function isFitGenerator($columnStructure) {
        return $columnStructure->dataType == 'timestamp';
    }
}

class IntValueGenerator extends ValueGenerator {
    private static $ranges = [
        'tinyint'   => [ false => [ 'min' => -128,                 'max' => 127                 ], true => [ 'min' => 0, 'max' => 255 ] ],
        'smallint'  => [ false => [ 'min' => -32768,               'max' => 32767               ], true => [ 'min' => 0, 'max' => 65535 ] ],
        'int'       => [ false => [ 'min' => -2147483648,          'max' => 2147483647          ], true => [ 'min' => 0, 'max' => 4294967295 ] ],
        'mediumint' => [ false => [ 'min' => -8388608,             'max' => 8388607             ], true => [ 'min' => 0, 'max' => 16777215 ] ]//,
//        'bigint'    => [ false => [ 'min' => -9223372036854775808, 'max' => 9223372036854775807 ], true => [ 'min' => 0, 'max' => 18446744073709551615 ] ]
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

class DecimalValueGenerator extends ValueGenerator {
	public function next() {
	 	return $this->quote(big_rand_decimal(65, 30));
	}

	public static function isFitGenerator($columnStructure) {
		return $columnStructure->dataType == 'decimal';
	}
}

class FloatValueGenerator extends ValueGenerator {
	public function next() {
	 	return $this->quote(big_rand_decimal(39, 7));
	}

	public static function isFitGenerator($columnStructure) {
		return $columnStructure->dataType == 'float';
	}
}

function str_repeat_func($function, $multiplier) {
    if(!is_callable($function) || !is_integer($multiplier) || $multiplier <= 0)
        return "";

    $accumulator = "";
    for($i = 0; $i < $multiplier; $i++)
        $accumulator .= call_user_func($function);

    return $accumulator;
}

function big_rand_integer($digits) {
    if(!is_numeric($digits) || $digits <= 0)
        return '0';

    if(!is_integer($digits))
        $digits = (int)ceil($digits);

    $multiplier = (int)ceil($digits / 9.0);

    $function = function() {
        return(str_pad(strrev((string)mt_rand()), 9, '0', STR_PAD_LEFT));
    };

    return substr(str_repeat_func($function, $multiplier), 0, $digits);
}

function big_rand_decimal($integerDigits, $decimalDigits, $decimalSeparator = ".") {
    if(!is_numeric($integerDigits) || $integerDigits <= 0)
        $integerDigits = 0;
    else if(!is_integer($integerDigits))
        $integerDigits = (int)ceil($integerDigits);

    if(!is_numeric($decimalDigits) || $decimalDigits <= 0)
        $decimalDigits = 0;
    else if(!is_integer($decimalDigits))
        $decimalDigits = (int)ceil($decimalDigits);

    if($integerDigits + $decimalDigits == 0)
        return '0' . $decimalSeparator . '0';

    $bigInteger = big_rand_integer($integerDigits + $decimalDigits);
    $bigDecimal = substr($bigInteger, 0, $integerDigits) . $decimalSeparator . substr($bigInteger, $integerDigits);

    return ($integerDigits == 0 ? '0' : '') . $bigDecimal . ($decimalDigits == 0 ? '0' : '');
}
