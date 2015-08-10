<?php

namespace MagicUtils;

/**
 * Provides a default implementation of __clone() which copies all member
 * properties like C++. This will only clone properties belonging to the class
 * in which it's used, meaning you must use the trait in each class that you
 * want it, even if the parent class is already using the trait. This is to
 * avoid "action at a distance" so the `use DeepClone;` line sits close to the
 * properties on which it acts. If present, the __clone() method of the parent
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

trait NoConstruct {
    function __construct() {
    }
}

trait NoDynamicMethods {
    function __call($name, $arguments) {
    }

    static function __callStatic($name, $arguments) {
    }
}

trait NoDynamicProperties {
    function __get($name) {
    }

    function __set($name, $value) {
    }

    function __isset($name) {
    }

    function __unset($name) {
    }
}

trait NoSerialize {
    function __sleep() {
    }

    function __wakeup() {
    }
}

trait AutoSetState {
    static function __set_state(array $props) {
        $class = new \ReflectionClass(get_called_class());
        $self  = $class->newInstanceWithoutConstructor();
        foreach ($props as $k => $v) {
            $self->$k = $v;
        }
        return $self;
    }
}

