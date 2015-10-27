<?php

namespace MagicUtils;

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


