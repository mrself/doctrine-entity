<?php declare(strict_types=1);

namespace Mrself\DoctrineEntity\Tests\Unit\Entity\EntityTrait;

use Doctrine\Common\Collections\ArrayCollection;
use Mrself\DoctrineEntity\EntityTrait;
use PHPUnit\Framework\TestCase;

class SetAssociationsTest extends TestCase
{
    public function testItSetsAssociations()
    {
        $entity = new class {
            use EntityTrait;

            public function __construct()
            {
                $this->childs = new ArrayCollection();
            }

            public function setChilds($associations = [])
            {
                $this->childs = new ArrayCollection($associations);
            }

            public function getChilds()
            {
                return $this->childs;
            }
        };
        $child = new class {};
        $entity->setChilds([$child]);
        $this->assertContains($child, $entity->getChilds());
    }
}