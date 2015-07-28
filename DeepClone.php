<?php

namespace JesseSchalken;

/**
 * Provides a default implementation of __clone() which copies all member properties like C++.
 */
trait DeepClone {
    function __clone() {
        clone_props($this);
    }
}

