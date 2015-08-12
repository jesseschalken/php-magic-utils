<?php

namespace MagicUtils;

use LogicException;

/**
 * Clones all the properties of an object.
 * @param object $object
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

/**
 * @param string $class
 * @param string $method
 * @return UndefinedMethodException
 */
function undefined_method($class, $method) {
    return new UndefinedMethodException("Call to undefined method $class::$method()");
}

/**
 * @param string $class
 * @param string $property
 * @return UndefinedPropertyException
 */
function undefined_property($class, $property) {
    return new UndefinedPropertyException("Undefined property: $class::$property");
}

/**
 * @param string $class
 * @return SerializeNotSupportedException
 */
function no_serialize($class) {
    return new SerializeNotSupportedException("Serialization of class $class is not supported");
}

/**
 * @param string $class
 * @return CloneNotSupportedException
 */
function no_clone($class) {
    return new CloneNotSupportedException("Clone of class $class is not supported");
}

class UndefinedMethodException extends LogicException {
}

class UndefinedPropertyException extends LogicException {
}

class SerializeNotSupportedException extends LogicException {
}

class CloneNotSupportedException extends LogicException {
}

