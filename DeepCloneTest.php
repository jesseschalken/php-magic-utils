<?php

namespace JesseSchalken\DeepCloneTest;

use JesseSchalken\DeepClone;

class A {
    use DeepClone;

    private $b;

    function __construct() {
        $this->b = [new B];
    }

    function getB() { return $this->b[0]; }
}

class A2 extends A {
}

class B {
    use DeepClone;
}

class DeepCloneTest extends \PHPUnit_Framework_TestCase {
    function testDeepClone() {
        $a1 = new A2;
        $a2 = clone $a1;
        self::assertNotEquals(
            spl_object_hash($a1->getB()),
            spl_object_hash($a2->getB())
        );
    }

    function testDynamic() {
        $a1 = new B;
        /** @noinspection PhpUndefinedFieldInspection */
        $a1->foo = new \stdClass;
        $a2 = clone $a1;
        /** @noinspection PhpUndefinedFieldInspection */
        self::assertNotEquals(
            spl_object_hash($a1->foo),
            spl_object_hash($a2->foo)
        );
    }
}

