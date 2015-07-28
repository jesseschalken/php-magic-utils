# deep-clone

Turn

```php
class Foo {
    /** Blah|null */
    private $blah;
    /** \DateTime[] */
    private $dates = [];

    // :( :( :(
    function __clone() {
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

class Foo {
    /** Blah|null */
    private $blah;
    /** \DateTime[] */
    private $dates = [];
    
    // :) :) :)
    use DeepClone;
}
```

That is all.
