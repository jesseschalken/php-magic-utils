<?php

namespace JesseSchalken\MagicUtils;

use LogicException;

class UndefinedMethodException extends LogicException {
    /**
     * @param string $class
     * @param string $method
     */
    function __construct($class, $method) {
        parent::__construct("Call to undefined method $class::$method()");
    }
}

class UndefinedPropertyException extends LogicException {
    /**
     * @param string $class
     * @param string $property
     */
    function __construct($class, $property) {
        parent::__construct("Undefined property: $class::$property");
    }
}

class SerializeNotSupportedException extends LogicException {
    /**
     * @param string $class
     */
    function __construct($class) {
        parent::__construct("Serialization of class $class is not supported");
    }
}

class CloneNotSupportedException extends LogicException {
    /**
     * @param string $class
     */
    function __construct($class) {
        parent::__construct("Clone of class $class is not supported");
    }
}

class ConstructNotSupportedException extends LogicException {
    /**
     * @param string $class
     */
    function __construct($class) {
        parent::__construct("Construction of $class is not supported");
    }
}

/**
 * Clones objects and arrays of objects (recursively).
 * @param mixed $ref
 * @throws \Exception
 */
function clone_ref(&$ref) {
    if (is_object($ref)) {
        $ref = clone $ref;
    } else if (is_array($ref)) {
        foreach ($ref as &$y) {
            clone_ref($y);
        }
    } else if (is_resource($ref)) {
        throw new \Exception("Resources cannot be cloned");
    }
}

/**
 * Clones objects and arrays of objects (recursively).
 * @param mixed $val
 * @return mixed
 */
function clone_val($val) {
    clone_ref($val);
    return $val;
}

