# php-magic-utils

**php-magic-utils** provides traits and functions to help with the implementation of [PHP's magic methods](http://php.net/manual/en/language.oop5.magic.php).

## Opting out of magic

Method|Default|Disallow
------|-------|--------
`__construct()`|_nothing_, construction allowed|
`__destruct()`|_nothing_|
`__call()`, `__callStatic()`|`Fatal error: Call to undefined method $class::$method()`|<code>use&nbsp;NoDynamicMethods;</code>
`__get()`, `__set()`, `__isset()`, `__unset()`|_Write:_ Create undeclared public properties (!)<br>_Read:_ `Undefined property: $class::$property`|<code>use&nbsp;NoDynamicProperties;</code>
`__sleep()`, `__wakeup()`|_nothing_, `serialize()`/`unserialize()` allowed|<code>use&nbsp;NoSerialize;</code>
`__toString()`|`Catchable fatal error: Object of class $class could not be converted to string`|
`__invoke()`|`Fatal error: Function name must be a string`|
`__set_state()`|`Fatal error: Call to undefined method $class:__set_state()`|
`__clone()`|shallow clone (for deep clone use <code>use&nbsp;DeepClone;</code>)|<code>use&nbsp;NoClone;</code>
`__debugInfo()`|`var_dump()` prints all public properties|

### `use NoDynamicMethods;`

The magic methods `__call()` and `__callStatic()` make adding new methods to a class potentially unsafe, since the new method may unintentionally override a dynamic method handled by `__call()` or `__callStatic()`.

`use NoDynamicMethods;` defines `__call()` and `__callStatic()` to throw an `UndefinedMethod` exception, so adding new methods is always safe.

### `use NoDynamicProperties;`

The magic methods `__get()`, `__set()`, `__isset()` and `__unset()` make adding new properties to a class potentially unsafe, since the new property may unintentionally override a dynamic property handled by these magic methods.

Even without these magic methods defined, a new property on a class may already be being used as an undeclared public property, for example:

```php
class Foo {
}
```
```php
function blah(Foo $foo) {
    $foo->bar = 5;
    return $foo->bar;
}
```

The usage of a undeclared property `Foo::$bar` in `blah()` has made it unsafe for the author of `Foo` to add `Foo::$bar` as a new property.

`use NoDynamicProperties;` defines `__get()`, `__set()`, `__isset()` and `__unset()` to throw an `UndefinedProperty` exception, so adding new properties is always safe.

### `use NoSerialize;`

PHP's builtin `serialize()` and `unserialize()` functions make it potentially unsafe to change a class's name or properties, because a serialized version of the class may exist which needs to continue to work when unserialized.

`use NoSerialize;` defines `__sleep()` and `__wakeup()` to throw a `SerializeNotSupported` exception so renaming a class or changing its properties is always safe.

### `use NoMagic;`

`use NoMagic;` disallows any magic which makes refactoring difficult. It is equivalent to

```php
use NoDynamicMethods;
use NoDynamicProperties;
use NoSerialize;
```

## Cloning objects

When an object is cloned with `clone ...`, PHP by default does a _shallow_ clone, meaning a new object is created with the same value for all properties, but sharing the same instance of any objects contained in those properties. This can expose the user of the class to be affected by what information is stored directly and what information is stored indirectly through other objects, breaking abstraction and causing subtle bugs.

### Deep clone

For example:

```php
class A {
    private $foo = 9;
    public function getFoo() { return $this->foo; }
    public function setFoo($foo) { $this->foo = $foo; }
}
```
```php
function test() {
    $a1 = new A;
    $a1->setFoo(100);
    $a2 = clone $a1;
    $a2->setFoo(200);
    print $a2->getFoo(); // 100
}
```

If a refactoring is made to move the value of the `$foo` property into another object (`B`):

```php
class A {
    private $b;
    public function __construct() {
        $this->b = new B;
    }
    public function getFoo() { return $this->b->foo; }
    public function setFoo($foo) { $this->b->foo = $foo; }
}

class B {
    public $foo = 9;
}
```

The `test()` function will now print _200_ instead of _100_, because both `$a1` and `$a2` will share the same instance of `B`.

This can be resolved by implementing `__clone()` by doing a deep clone:

```php
class A {
    private $b;
    // ...
    public function __clone() {
        $this->b = clone $this->b;
    }
    // ...
}
```

`use DeepClone;` will do this for you.

### No clone

Another example is a class which contains a unique ID based on a global counter:

```php
class A {
    private $id;
    public function __construct() {
        static $id = 1;
        $this->id = $id++;
    }
    public function getID() {
        return $this->id;
    }
    // ...
}

function test() {
    $a1 = new A;
    $a2 = clone $a1;
    echo $a1->getID(); // 1
    echo $a2->getID(); // 1
}
```

In this case, the `clone ...` has allowed multiple instances to share the same ID, and can be resolved by preventing instances of this class from being cloned altogether. `use NoClone;` will do this for you.

### Mixed shallow/deep clone

Other situations require some properties to be cloned and others to be shared, such as data structures sharing a common resource. In these cases `__clone()` must be implemented manually cloning only the properties required, using `$this->prop = clone_val($this->prop);` or `clone_ref($this->prop);`.

For example:

```php
class A {
    private $prop1;
    private $prop2;
    function __construct() {
        $this->prop1 = new Foo1;
        $this->prop2 = new Foo2;
    }
    function __clone() {
        clone_ref($this->prop2);
        // cloned instances will share the same object stored in $this->prop1
    }
}
```

### `use NoClone;`

`use NoClone;` prevents an object from being cloned. It implements `__clone()` by throwing a `CloneNotSupportedException`.

### `use DeepClone;`

`use DeepClone;` turns

```php
class Foo extends Bar {
    /** @var Blah|null */
    private $blah;
    /** @var \DateTime[] */
    private $dates = [];

    // :( :( :(
    function __clone() {
        parent::__clone();

        if ($this->blah !== null)
            $this->blah = clone $this->blah;
        
        foreach ($this->dates as $k => $date)
            $this->dates[$k] = clone $date;
    }
}
```

into

```php
use \JesseSchalken\DeepClone;

class Foo extends Bar {
    /** @var Blah|null */
    private $blah;
    /** @var \DateTime[] */
    private $dates = [];
    
    // :) :) :)
    use DeepClone;
}
```

It implements `__clone()` by calling `parent::__clone()` if it exists, and cloning all objects contained in all properties of the class in which it's used, including objects in arbitrarily nested arrays. It will error if it finds a `resource` type, since `resource`s are pass-by-reference like objects and therefore should be cloned, but there is no general way to clone a `resource`.

Note that you have to use `use DeepClone;` at each level in a class hierarhcy. It will not clone properties of parent or derived classes.

### `clone_ref()`, `clone_val()`, `clone_props()`

- `clone_ref(mixed &$var):void`

  Will clone all objects contained in the specified variable, including those inside nested arrays. It is useful for   implementing `__clone()` by deep cloning only some properties.

- `clone_val(mixed $val):mixed`

  Will clone all objects contained in the specified value, and return the new value.

- `clone_props(object $object, [string $class]):void`

   Clones all the objects contained in the properties of the specified object. If `$class` is specified, it will only clone properties defined in that class, and not properties defined in other classes in the hierarchy. 
