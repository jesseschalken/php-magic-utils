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
 * Clones all the properties of an object.
 * @param object      $object
 * @param string|null $filter If specified, only clone properties belonging to this class.
 */
function clone_props($object, $filter = null) {
    $class = $filter === null || $filter === get_class($object)
        ? new \ReflectionObject($object)
        : new \ReflectionClass($filter);
    do {
        foreach ($class->getProperties() as $prop) {
            if (!$prop->isStatic() && $prop->class === $class->name) {
                $prop->setAccessible(true);
                $prop->setValue($object, clone_val($prop->getValue($object)));
            }
        }
    } while ($filter !== null && $class = $class->getParentClass());
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
        foreach ($ref as &$y)
            clone_ref($y);
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

