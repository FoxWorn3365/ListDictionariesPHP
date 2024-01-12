<?php
/* 
|
| List Dictionaries for PHP by FoxWorn3365
|
*/

namespace ListDictionariesPHP;

use InvalidArgumentException;

class PHPList implements \IteratorAggregate {
    protected array $data;
    protected int $type;

    public function __construct(string $type, array $data) {
        $this->type = Type::parser($type);
        foreach ($data as $child) {
            if (!Type::validate($this->type, $child)) {
                throw new InvalidArgumentException("Found an invalid argument at PHPList::__construct function, expecting " . Type::list($this->type) . ", got " . Type::get($child));
                return;
            }
        }
        $this->data = $data;
    }

    public static function new(string $type = "mixed", array $data = []) : self {
        return new PHPList($type, $data);
    }

    public function getIterator() : \ArrayIterator {
        return new \ArrayIterator($this->data);
    }

    public function getList() : array {
        return $this->data;
    }

    public function add(mixed ...$vars) : self {
        foreach ($vars as $var) {
            if (Type::validate($this->type, $var)) {
                $this->data[] = $var;
                continue;
            }
            throw new InvalidArgumentException("Found an invalid argument at PHPList::add function, expecting " . Type::list($this->type) . ", got " . Type::get($var));
        }
        return $this;
    }

    public function push(array|PHPList $data) : self {
        if ($data instanceof PHPList) {
            $data = $data->getList();
        }
        foreach ($data as $element) {
            $this->add($element);
        }
        return $this;
    }

    public function count() : int {
        return count($this->data);
    }

    public function find(mixed $value) : ?int {
        if (Type::validate($this->type, $value)) {
            for ($a = 0; $a < $this->count(); $a++) {
                if ($this->data[$a] === $value) {
                    return $a;
                }
            }
            return null;
        }
        throw new InvalidArgumentException("Found an invalid argument at PHPList::add function, expecting " . Type::list($this->type) . ", got " . Type::get($value));
    }

    private function internalRemove(mixed $value) : bool {
        if ($index = $this->find($value) === null) {
            return false;
        }
        unset($this->data[$index]);
        return true;
    }

    public function remove(mixed $value) : self {
        while ($this->internalRemove($value) == true) {}
        return $this;
    }

    public function sort() : self {
        sort($this->data);
        return $this;
    }

    public function asort() : self {
        asort($this->data);
        return $this;
    }

    public function where(\Closure $arrow) : PHPList {
        $new = PHPList::new(Type::list($this->type));
        foreach ($this->data as $data) {
            if ($arrow($data) == true) {
                $new->add($data);
            }
        }
        return $new;
    }

    public function chunk(int $length = 2) : PHPList {
        $chunks = array_chunk($this->data, $length);
        $data = [];
        foreach ($chunks as $chunk) {
            $data[] = PHPList::new(Type::list($this->type), $chunk);
        }
        return PHPList::new("list", $data);
    }

    public function diff(PHPList|null|bool &$out = null, array|PHPList ...$arrays) : array|self {
        $diff = array_diff($this->data, $arrays);
        if ($out instanceof PHPList) {
            $out->push($diff);
        } elseif ($out === null || !$out) {
            return $diff;
        } elseif ($out) {
            $this->data = $diff;
        }
        return $this;
    }

    public function random() : mixed {
        return $this->data[rand(0, $this->count()-1)];
    }

    public function randomItem() : mixed {
        return $this->random();
    }

    public function slice(int $offset, ?int $lenght = null) : PHPList {
        return PHPList::new(Type::list($this->type), array_slice($this->data, $offset, $lenght));
    }

    public function first() : mixed {
        if ($this->count() > 0) {
            return $this->data[0];
        }
        return null;
    }

    public function last() : mixed {
        if ($this->count() > 0) {
            return $this->data[$this->count()-1];
        }
        return null;
    }

    public function pop() : self {
        $this->remove($this->last());
        return $this;
    }

    public function shift() : self {
        $this->remove($this->first());
        return $this;
    }

    public function contains(mixed ...$data) : bool {
        $contains = true;
        foreach ($data as $element) {
            if (!Type::validate($this->type, $element)) {
                throw new InvalidArgumentException("Found an invalid argument at PHPList::add function, expecting " . Type::list($this->type) . ", got " . Type::get($element));
                return false;
            }
            $contains = in_array($element, $this->data) && $contains;
        }
        return $contains;
    }

    public function get(int $offset = 0) : mixed {
        if ($offset >= $this->count()) {
            return null;
        }
        return $this->data[$offset];
    }

    public function clear(array|PHPList $new = []) : self {
        if ($new instanceof PHPList) {
            $this->data = $new->getList();
        } else {
            $this->data = $new;
        }  
        return $this;
    }

    public function foreach(callable $function) : self {
        foreach ($this->data as $value) {
            if ($function($value) == ReturnAction::BREAK) {
                break;
            }
        }
        return $this;
    }
}