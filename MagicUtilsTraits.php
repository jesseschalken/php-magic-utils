<?php

/**
 * Traits should be in a separate file to be autoloaded so the library is usable on PHP 5.3
 */
namespace JS\MagicUtils;

/**
 * Provides a default implementation of __clone() which copies all member properties like C++. This will only clone
 * properties belonging to the class in which it's used, meaning you must use the trait in each class that you want
 * it, even if the parent class is already using the trait. This is to avoid "action at a distance" so the `use
 * DeepClone;` line sits close to the properties on which it acts. If present, the __clone() method of the parent
 * class will be called.
 */
trait DeepClone {
    function __clone() {
        $parent = get_parent_class(__CLASS__);
        if ($parent && method_exists($parent, '__clone')) {
            $method = new \ReflectionMethod($parent, '__clone');
            $method->invoke($this);
        }

        clone_props($this, __CLASS__);
    }
}

trait NoDynamicMethods {
    static function __callStatic($name, $arguments) {
        throw new UndefinedMethodException(__CLASS__, $name);
    }

    function __call($name, $arguments) {
        throw new UndefinedMethodException(__CLASS__, $name);
    }
}

trait NoDynamicProperties {
    function __get($name) {
        throw new UndefinedPropertyException(__CLASS__, $name);
    }

    function __set($name, $value) {
        throw new UndefinedPropertyException(__CLASS__, $name);
    }

    function __isset($name) {
        throw new UndefinedPropertyException(__CLASS__, $name);
    }

    function __unset($name) {
        throw new UndefinedPropertyException(__CLASS__, $name);
    }
}

trait NoSerialize {
    function __sleep() {
        throw new SerializeNotSupportedException(__CLASS__);
    }

    function __wakeup() {
        throw new SerializeNotSupportedException(__CLASS__);
    }
}

trait NoClone {
    function __clone() {
        throw new CloneNotSupportedException(__CLASS__);
    }
}

trait NoMagic {
    use NoDynamicMethods;
    use NoDynamicProperties;
    use NoSerialize;
}
