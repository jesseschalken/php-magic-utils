<?php

namespace JesseSchalken;

/**
 * Clones all the properties of an object.
 * @param object $object
 */
function clone_props($object) {
    $class = new \ReflectionObject($object);
    do {
        foreach ($class->getProperties() as $prop) {
            if (!$prop->isStatic() && $prop->class === $class->name) {
                $prop->setAccessible(true);
                $prop->setValue($object, clone_val($prop->getValue($object)));
            }
        }
    } while ($class = $class->getParentClass());
}

/**
 * Clones objects and arrays of objects (recursively).
 * @param mixed $ref
 */
function clone_ref(&$ref) {
    if (is_object($ref)) {
        $ref = clone $ref;
    } else if (is_array($ref)) {
        foreach ($ref as &$y)
            clone_ref($y);
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

