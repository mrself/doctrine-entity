<?php declare(strict_types=1);

namespace Mrself\DoctrineEntity\Tests\Unit\Entity\AssociationSetterTest;

use Mrself\DoctrineEntity\AssociationSetter\AssociationSetter;
use Mrself\DoctrineEntity\EntityInterface;
use Mrself\DoctrineEntity\EntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class AssociationSetterTest extends TestCase
{
    public function testItAddItemToRelativeCollection()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::autoRun('target');
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }
        };

        $association = new class {
            var $isCalled = false;
            function setTarget($target) {
                $this->isCalled = true;
            }
        };
        $owner->setRelativeItems([$association]);
        $this->assertContains($association, $owner->relativeItems);
    }

    public function testItCallsAddMethodOnInverseSide()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::autoRun('target');
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }
        };

        $association = new class {
            var $isCalled = false;
            function setTarget($target) {
                $this->isCalled = true;
            }
        };
        $owner->setRelativeItems([$association]);

        $this->assertTrue($association->isCalled);
    }

    public function testItDoesNotAddItemToRelativeCollectionIfCollectionHasIt()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::autoRun('target');
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }
        };

        $association = new class {
            var $isCalled = false;
            function setTarget($target) {
                $this->isCalled = true;
            }
        };
        $owner->relativeItems = new ArrayCollection([$association]);
        $owner->setRelativeItems([$association]);

        $this->assertContains($association, $owner->relativeItems);
        $this->assertCount(1, $owner->relativeItems);
        $this->assertFalse($association->isCalled);
    }

    public function testItRemovesItemFromCollectionWhichDoesNotExistInParam()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::autoRun('target');
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }
        };

        $association = new class {
            var $isCalled = false;
            function setTarget($target) {
                $this->isCalled = true;
            }
        };
        $owner->relativeItems = new ArrayCollection([$association]);
        $owner->setRelativeItems([]);

        $this->assertNotContains($association, $owner->relativeItems);
        $this->assertCount(0, $owner->relativeItems);
        $this->assertFalse($association->isCalled);
    }

    /**
     * @expectedException \Mrself\DoctrineEntity\AssociationSetter\InvalidAssociationException
     */
    public function testItThrowsExceptionIfThereIsNoInverseMethod()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::autoRun('target');
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }
        };

        $association = new class {};
        $owner->setRelativeItems([$association]);
    }

    public function testItCallsAddAsInverseMethod()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::autoRun('target');
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }
        };

        $association = new class {
            var $isCalled = false;
            function addTarget($target) {
                $this->isCalled = true;
            }
        };
        $owner->setRelativeItems([$association]);

        $this->assertTrue($association->isCalled);
    }

    public function testItDefinesInverseName()
    {
        $owner = new MockEntity();
        $association = new class {
            var $isCalled = false;
            function addMock($target) {
                $this->isCalled = true;
            }
        };
        $owner->setRelativeItems([$association]);

        $this->assertTrue($association->isCalled);
    }

}