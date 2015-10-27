<?php

namespace MagicUtils\Test;

use MagicUtils\DeepClone;
use MagicUtils\NoClone;
use MagicUtils\NoMagic;
use MagicUtils\NoSerialize;

class A {
    use DeepClone;
    use NoMagic;

    private $b;

    function __construct() {
        $this->b = [new B];
    }

    function getB() { return $this->b[0]; }
}

class A2 extends A {
}

class A3 extends A2 {
    use DeepClone;
}

class B {
    use DeepClone;
}

class NotCloneable {
    use NoClone;
}

class NotSerializable {
    use NoSerialize;
}

class Test extends \PHPUnit_Framework_TestCase {
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
        $a2      = clone $a1;
        /** @noinspection PhpUndefinedFieldInspection */
        self::assertNotEquals(
            spl_object_hash($a1->foo),
            spl_object_hash($a2->foo)
        );
    }

    /**
     * @expectedException \MagicUtils\UndefinedPropertyException
     * @expectedExceptionMessage Undefined property: MagicUtils\Test\A::foo
     */
    function testGet() {
        $a = new A;
        /** @noinspection PhpUndefinedFieldInspection */
        $a->foo;
    }

    /**
     * @expectedException \MagicUtils\UndefinedPropertyException
     * @expectedExceptionMessage Undefined property: MagicUtils\Test\A::foo
     */
    function testSet() {
        $a      = new A;
        /** @noinspection PhpUndefinedFieldInspection */
        $a->foo = 4;
    }

    /**
     * @expectedException \MagicUtils\UndefinedPropertyException
     * @expectedExceptionMessage Undefined property: MagicUtils\Test\A::foo
     */
    function testUnset() {
        $a = new A;
        unset($a->foo);
    }

    /**
     * @expectedException \MagicUtils\UndefinedPropertyException
     * @expectedExceptionMessage Undefined property: MagicUtils\Test\A::foo
     */
    function testIsset() {
        $a = new A;
        if (isset($a->foo)) {
        }
    }

    /**
     * @expectedException \MagicUtils\UndefinedMethodException
     * @expectedExceptionMessage Call to undefined method MagicUtils\Test\A::foo()
     */
    function testCall() {
        $a = new A;
        /** @noinspection PhpUndefinedMethodInspection */
        $a->foo();
    }

    /**
     * @expectedException \MagicUtils\UndefinedMethodException
     * @expectedExceptionMessage Call to undefined method MagicUtils\Test\A::foo()
     */
    function testCallStatic() {
        /** @noinspection PhpUndefinedMethodInspection */
        A::foo();
    }

    /**
     * @expectedException \MagicUtils\SerializeNotSupportedException
     * @expectedExceptionMessage Serialization of class MagicUtils\Test\NotSerializable is not supported
     */
    function testSleep() {
        serialize(new NotSerializable);
    }

    /**
     * @expectedException \MagicUtils\SerializeNotSupportedException
     * @expectedExceptionMessage Serialization of class MagicUtils\Test\NotSerializable is not supported
     */
    function testWakeup() {
        unserialize('O:31:"MagicUtils\Test\NotSerializable":0:{}');
    }

    /**
     * @expectedException \MagicUtils\CloneNotSupportedException
     * @expectedExceptionMessage Clone of class MagicUtils\Test\NotCloneable is not supported
     */
    function testNoClone() {
        /** @noinspection PhpExpressionResultUnusedInspection */
        clone new NotCloneable;
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Resources cannot be cloned
     */
    function testCloneResource() {
        \MagicUtils\clone_val(fopen('php://memory', 'rb'));
    }

    function testCloneParent() {
        /** @noinspection PhpExpressionResultUnusedInspection */
        clone new A3;
    }
}

