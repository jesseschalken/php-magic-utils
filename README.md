# deep-clone

Turns

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

### `DeepClone`

The `DeepClone` trait implements `__clone()` by cloning all objects in the properties of the class in which it is used, including objects inside (arbitrarily nested) arrays. It will call `parent::__clone()` if it exists.

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
