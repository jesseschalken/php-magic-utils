## php-magic-utils

**php-magic-utils** provides traits and functions to help with the implementation of [PHP's magic methods](http://php.net/manual/en/language.oop5.magic.php).

Method|Default|Disallow
------|-------|--------
`__construct()`|_nothing_, construction allowed|
`__destruct()`|_nothing_|
`__call()`, `__callStatic()`|"Fatal error: Call to undefined method <br>_class_::_method_()"|<code>use&nbsp;NoDynamicMethods;</code>
`__get()`, `__set()`, `__isset()`, `__unset()`|_Write:_ Create undeclared public properties<br>_Read:_ "Undefined property: $class::$property"|<code>use&nbsp;NoDynamicProperties;</code>
`__sleep()`, `__wakeup()`|_nothing_, `serialize()`/`unserialize()` allowed|<code>use&nbsp;NoSerialize;</code>
`__toString()`|"Catchable fatal error: Object of class<br> _class_ could not be converted to string"|
`__invoke()`|"Fatal error: Function name must be a string"|
`__set_state()`|"Fatal error: Call to undefined method _class_::__set_state()"|
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

function blah(Foo $foo) {
    $foo->bar = 5;
    return $foo->bar;
}
```

The usage of a undeclared property `Foo::$bar` in `blah()` has made it unsafe to add `Foo::$bar` as a new property.

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

### `use NoClone;`

To disallow an object from being cloned, use `use NoClone;`.

For example, if an object contains a `resource` which it is supposed to have unique ownership of, `clone` would violate this by creating another object sharing the same resource.

Another example is a class which has a unique ID based on a static counter:

```php
class A {
    private $id;

    public function __construct() {
        static $id = 1;
        $this->id = $id++;
    }

    // ...
}
```

Cloning an instance of `A` will result in two objects containing the same unique ID.

`use NoClone;` is useful in these cases.

### `use DeepClone;`

PHP by default implements object cloning by doing a _shallow_ clone. That is, a new object is created with the same value for all properties, but the new object continues to reference the same copy of any objects contained in those properties. This is a poor choice for the default implementation of `clone`.

Consider the following class:

```php
class A {
    private $foo = 9;

    public function getFoo() { return $this->foo; }
    public function setFoo($foo) { $this->foo = $foo; }
}
```

And the following function, which prints _100_:

```php
function blah() {
    $a1 = new A;
    $a1->setFoo(100);

    $a2 = clone $a1;
    $a2->setFoo(200);

    print $a2->getFoo(); // 100
}
```

If we were to extract a class containing the `$foo` property, and then re-implement `getFoo` and `setFoo`, like so:

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

This is a breaking change. `blah()` will now print _200_ instead of _100_ because `$a1` and `$a2` will share the same instance of `B`.

This could be fixed by adding an implementation of `__clone()` to `A` which does a deep clone:

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

Now `blah()` will print _100_ again. `use DeepClone;` automates this, so you don't have to remember to correctly implement `__clone()`.

`use DeepClone;` implements `__clone()` by calling `parent::__clone()` if it exists, and cloning all objects contained in all properties of the class in which it's used, including objects in arbitrarily nested arrays. It will error if it finds a `resource` type, since `resource`s are pass-by-reference like objects and therefore should be cloned, but there is no general way to clone a `resource`.

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

### `clone_ref()`, `clone_val()`

If you want to implement `__clone()` by cloning objects in some, but not all, properties, you can use `clone_ref()`:

```php
use function \JesseSchalken\clone_ref;

class Foo {
    // ...
    function __clone() {
        clone_ref($this->prop1);
        clone_ref($this->prop2);
        // ...
    }
    // ...
}
```

or `clone_val()`:

```php
use function \JesseSchalken\clone_val;

class Foo {
    // ...
    function __clone() {
        $this->prop1 = clone_val($this->prop1);
        $this->prop2 = clone_val($this->prop2);
        // ...
    }
    // ...
}
```

`clone_val()` and `clone_ref()` are safe to call on all PHP types except `resource` and of course any object which throws an error when cloned (such as `\Closure`).

### `clone_props()`

If you want to implement `__clone()` by cloning objects in all properties, _including_ properties of parent/derived classes, you can use `clone_props()`:

```php
use function \JesseSchalken\clone_props;

class Foo {
    // ...
    function __clone() {
        clone_props($this);
    }
    // ...
}
```

I don't recommend doing this, though, since other classes in the hierarchy may not expect to have objects in their properties cloned, and may define their own `__clone()` method with intentionally different semantics.
