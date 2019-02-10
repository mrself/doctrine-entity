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
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'target',
                    'relativeItems'
                );
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
            function getTarget()
            {
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
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'target',
                    'relativeItems'
                );
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
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'target',
                    'relativeItems'
                );
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
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'target',
                    'relativeItems'
                );
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
            function getTarget()
            {
                return new ArrayCollection();
            }
        };
        $owner->relativeItems = new ArrayCollection([$association]);
        $owner->setRelativeItems([]);

        $this->assertNotContains($association, $owner->relativeItems);
        $this->assertCount(0, $owner->relativeItems);
        $this->assertFalse($association->isCalled);
    }

    public function testItCalesRemoveMethodOnInverseSideIfItExists()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            var $isRemoveCalled = false;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'target',
                    'relativeItems'
                );
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }

            function removeRelativeItem($relativeItem)
            {
                $this->isRemoveCalled = true;
            }
        };

        $association = new class {
            public function __construct()
            {
                $this->collection = new ArrayCollection();
            }

            function addTarget($target) {
                $this->collection->add($target);
            }
            function removeTarget($target) {
                $this->collection->remove($target);
            }

        };
        $owner->setRelativeItems([$association]);
        $owner->setRelativeItems([]);

        $this->assertTrue($owner->isRemoveCalled);
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
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'target',
                    'relativeItems'
                );
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
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'target',
                    'relativeItems'
                );
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
}