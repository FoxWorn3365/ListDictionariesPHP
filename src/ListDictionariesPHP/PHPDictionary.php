<?php
/* 
|
| List Dictionaries for PHP by FoxWorn3365
|
*/

namespace ListDictionariesPHP;

use ArrayObject;
use InvalidArgumentException;

class PHPDictionary implements \IteratorAggregate {
    protected \ArrayObject $data;
    protected int $keytype;
    protected int $valuetype;

    public function __construct(string $keytype, string $valuetype, array|object $data) {
        $this->keytype = Type::parser($keytype);
        $this->valuetype = Type::parser($valuetype);
        if (gettype($data) == "array" && !self::validateArray($data)) {
            throw new InvalidArgumentException("Found an invalid argument at PHPList::__construct function, 2nd arg, expecting an object or associative array, got a plain array");
            return;
        }
        foreach ($data as $key => $value) {
            if (!$this->validate($key, $value)) {
                throw new InvalidArgumentException($this->argError($key, $value));
                return;
            }
        }
        $this->data = new \ArrayObject($data);
    }

    public static function new(string $keytype = "mixed", string $valuetype = "mixed", array|object $data = []) : self {
        return new PHPDictionary($keytype, $valuetype, $data);
    }

    protected static function validateArray(array $array) : bool {
        if (count(array_keys($array)) < 1) {
            return false;
        }
        return true;
    }

    protected function argError(mixed $key, mixed $value) : string {
        return "Found an invalid argument at PHPList::__construct function, expecting <" . Type::list($this->keytype) . ", " . Type::list($this->valuetype) . ">, got <" . Type::get($key) . ", " . Type::get($value) . "> ";
    }

    protected function validate(mixed $key, mixed $value) : bool {
        return Type::validate($this->keytype, $key) && Type::validate($this->valuetype, $value);
    }

    public function getIterator() : \ArrayIterator {
        return new \ArrayIterator($this->data);
    }

    public function getList() : object {
        return $this->data;
    }

    public function set(mixed $key, mixed $value) : self {
        if (!$this->validate($key, $value)) {
            throw new InvalidArgumentException($this->argError($key, $value));
            return $this;
        }
        $this->data->{$key} = $value;
        return $this;
    }

    public function add(mixed $key, mixed $value) : self {
        return $this->set($key, $value);
    }

    public function push(object|array $data, bool $override = false) : ?self {
        if (!self::validateArray($data)) {
            throw new InvalidArgumentException("Found an invalid argument at PHPList::__construct function, 1st arg, expecting an object or associative array, got a plain array");
            return null;
        }
        foreach ($data as $key => $value) {
            if (!$this->validate($key, $value)) {
                throw new InvalidArgumentException($this->argError($key, $value));
                return null;
            }
            if ($override && isset($this->data->{$key})) {
                continue;
            }
            $this->data->{$key} = $value;
        }
    }

    public function sort() : self {
        $data = $this->data->getArrayCopy();
        sort($data);
        $this->data = $data;
        return $this;
    }

    public function asort() : self {
        $this->data->asort();
        return $this;
    }

    public function rsort() : self {
        $data = $this->data->getArrayCopy();
        rsort($data);
        $this->data = $data;
        return $this;
    }

    public function ksort() : self {
        $data = $this->data->getArrayCopy();
        ksort($data);
        $this->data = $data;
        return $this;
    }

    public function arsort() : self {
        $data = $this->data->getArrayCopy();
        arsort($data);
        $this->data = $data;
        return $this;
    }

    public function krsort() : self {
        $data = $this->data->getArrayCopy();
        krsort($data);
        $this->data = $data;
        return $this;
    }

    public function where(callable $arrow) : PHPDictionary {
        $new = PHPDictionary::new($this->keytype, $this->valuetype);
        foreach ($this->data as $key => $value) {
            if ($arrow($key, $value)) {
                $new->add($key, $value);
            }
        }
        return $new;
    }

    protected function singleRemove(mixed $data) : void {
        if (isset($this->data->{$data})) {
            unset($this->data->{$data});
        }
    }

    public function remove(mixed ...$keys) : self {
        foreach ($keys as $key) {
            $this->singleRemove($key);
        }
        return $this;
    }

    public function get(mixed $key) : mixed {
        if (isset($this->data->{$key})) {
            unset($this->data->{$key});
        }
        return null;
    }

    public function containsKey(mixed ...$keys) : bool {
        $contains = true;
        foreach ($keys as $key) {
            if (!Type::validate($this->keytype, $key)) {
                throw new InvalidArgumentException("Found an invalid argument at PHPDictionary::@i@ContainsKey function, expecting " . Type::list($this->keytype) . ", got " . Type::get($key));
                return false;
            }
            $contains = isset($this->data->{$key}) && $contains;
        }
        return $contains;
    }

    protected function internalContain(mixed $value) : bool {
        foreach ($this->data as $_k => $val) {
            if ($val == $value) {
                return true;
            }
        }
        return false;
    }

    public function contains(mixed ...$values) : bool {
        $contains = true;
        foreach ($values as $value) {
            if (!Type::validate($this->valuetype, $value)) {
                throw new InvalidArgumentException("Found an invalid argument at PHPDictionary::@i@ContainsKey function, expecting " . Type::list($this->valuetype) . ", got " . Type::get($value));
                return false;
            }
            $contains = $contains && $this->internalContain($value);
        }
        return $contains;
    }

    public function has(mixed $value) : bool {
        if (!Type::validate($this->keytype, $value)) {
            throw new InvalidArgumentException("Found an invalid argument at PHPDictionary::has function, expecting " . Type::list($this->keytype) . ", got " . Type::get($value));
            return false;
        }
        return isset($this->data->{$value});
    }

    public function first() : mixed {
        foreach ($this->data as $_k => $value) {
            return $value;
        }
    }

    public function firstKey() : mixed {
        foreach ($this->data as $key => $_v) {
            return $key;
        }
    }

    public function last() : mixed {
        foreach ($this->data as $_k => $value) {}
        return $value;
    }

    public function lastKey() : mixed {
        foreach ($this->data as $key => $_v) {}
        return $key;
    }

    public function values(bool $toArray = false) : PHPList|array {
        $data = array_values($this->data->getArrayCopy());
        if ($toArray) {
            return $data;
        }
        return PHPList::new($this->keytype, $data);
    }

    public function random() : mixed {
        return $this->values()->random();
    }

    public function randomValue() : mixed {
        return $this->random();
    }

    public function keys(bool $toArray = false) : PHPList|array {
        $data = array_keys($this->data->getArrayCopy());
        if ($toArray) {
            return $data;
        }
        return PHPList::new($this->keytype, $data);
    }

    public function randomKey() : mixed {
        return $this->keys()->random();
    }

    public function foreach(callable $function) : self {
        foreach ($this->data as $key => $value) {
            if ($function($key, $value) == ReturnAction::BREAK) {
                break;
            }
        }
        return $this;
    }

    public function encode() : string {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }
}