# ListDictionariesPHP
`List<T>` and `Dictionary<T, T>` in PHP

## Install
You can install it on composer
```shell
composer require foxworn3365/listdictionaries
```

## Examples
### List
```php
$list = PHPList::new("string|int", [
    "hello",
    "goodbye",
    "your name is owo"
]);

$list->where(fn($el) => strpos($el, "e"))->foreach(function ($el) {
    echo $el . PHP_EOL;
});
```
Result:
```plain
hello
your name is owo
```