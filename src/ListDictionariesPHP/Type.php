<?php
/* 
|
| List Dictionaries for PHP by FoxWorn3365
|
*/

namespace ListDictionariesPHP;

class Type {
    public const STRING = 1;
    public const INT = 2;
    public const FLOAT = 3;
    public const BYTE = 3;
    public const BOOL = 4;
    public const ARRAY = 5;
    public const OBJECT = 6;
    public const RESOURCE = 7;
    public const UNKNOWN = -1;
    public const LIST = 8;
    public const DICTIONARY = 9;
    public const MIXED = 0;

    public static array $dictionary = [
        "boolean" => "bool",
        "integer" => "int",
        "INTEGER" => "int",
        "BOOLEAN" => "bool",
        "DOUBLE" => "float",
        "double" => "float"
    ];

    public static function get(mixed $var, bool $doId = false) : int|string {
        $type = gettype($var);
        if (isset(self::$dictionary[$type])) {
            $type = self::$dictionary[$type];
        }
        if ($type == "object") {
            if ($var instanceof PHPList) {
                $type = "list";
            }
        }
        if (!$doId) {
            return strtoupper($type);
        } else {
            return constant("self::" . strtoupper($type)) ?? Type::UNKNOWN;
        }
    }

    public static function parser(string $types) : int {
        if (strpos($types, "|") !== null) {
            $types = explode('|', $types);
        } else {
            $types = [
                $types
            ];
        }
        $code = "";
        foreach ($types as $type) {
            if (!defined("self::" . strtoupper($type))) {
                throw new \Exception("Found an invalid type while parsing the string type: '{$type}'");
            }
            $code .= constant("self::" . strtoupper($type));
        }
        return (int)$code;
    }

    public static function validate(int $types, mixed $var) : bool {
        $type = self::get($var, true);
        if (strpos($types, $type) === false) {
            return false;
        }
        return true;
    }

    public static function list(int $types) : string {
        $reflect = new \ReflectionClass(get_class(new Type()));
        $const = $reflect->getConstants();
        $result = "";
        foreach (str_split($types) as $char) {
            foreach ($const as $key => $value) {
                if ($value == $char) {
                    $result .= "{$key}|";
                }
            }
        }
        $result .= "-";
        return str_replace("|-", "", $result);
    }
}