## php-magic-utils

**php-magic-utils** provides traits and functions to help with the implementation of [PHP's magic methods](http://php.net/manual/en/language.oop5.magic.php).

<table>

<tr>
<th>Method</th>
<th>Default</th>
<th>Disallow</th>
</tr>

<tr>
<td><code>__construct()</code></td>
<td><i>nothing</i>, construction allowed</td>
<td><code>use NoConstruct;</code></td>
</tr>

<tr>
<td><code>__destruct()</code></td>
<td><i>nothing</i></td>
<td></td>
</tr>

<tr>
<td><code>__call()</code>, <code>__callStatic()</code></td>
<td><code>Fatal error: Call to undefined method [class]::[method]()</code></td>
<td><code>use NoDynamicMethods;</code></td>
</tr>

<tr>
<td>
<code>__get()</code>,
<code>__set()</code>,
<code>__isset()</code>,
<code>__unset()</code>
</td>
<td>Read/write dynamic public properties</td>
<td><code>use NoDynamicProperties;</code></td>
</tr>

<tr>
<td>
<code>__sleep()</code>,
<code>__wakeup()</code>
</td>
<td><i>nothing</i>, <code>serialize()</code>/<code>unserialize()</code> allowed</td>
<td><code>use NoSerialize;</code></td>
</tr>

<tr>
<td>
<code>__toString()</code>
</td>
<td><code>Catchable fatal error: Object of class [class] could not be converted to string</code></td>
<td><code>use NoToString;</code></td>
</tr>

<tr>
<td>
<code>__invoke()</code>
</td>
<td><code>Fatal error: Function name must be a string</code></td>
<td><code>use NoInvoke;</code></td>
</tr>

<tr>
<td>
<code>__set_state()</code>
</td>
<td><code>Fatal error: Call to undefined method [class]::__set_state()</code></td>
<td><code>use NoSetState;</code></td>
</tr>

<tr>
<td>
<code>__clone()</code>
</td>
<td><li>Shallow clone: <i>default</i></li><li>Deep clone: <code>use DeepClone;</code></li></td>
<td><code>use NoClone;</code></td>
</tr>

<tr>
<td>
<code>__debugInfo()</code>
</td>
<td><code>var_dump()</code> prints all public properties</td>
<td><code>use NoVarDump;</code></td>
</tr>

</table>

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

`use NoSerialize;` defines `__sleep()` and `__wakeup()` to throw a `SerializeNotSupported` exception so renaming a class or changing its properties is always safe (provided you update the usages).

### `NoMagic`

`use NoMagic;` disallows any magic which makes refactoring difficult. It is equivalent to `use NoDynamicMethods, NoDynamicProperties, NoSerialize;`.

### `DeepClone`

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
