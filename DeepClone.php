<?php

namespace JesseSchalken;

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
        if (is_callable('parent::__clone'))
            parent::__clone();
        clone_props($this, __CLASS__);
    }
}

