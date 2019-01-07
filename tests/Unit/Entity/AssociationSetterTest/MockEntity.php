<?php declare(strict_types=1);

namespace Mrself\DoctrineEntity\Tests\Unit\Entity\AssociationSetterTest;

use Mrself\DoctrineEntity\AssociationSetter\AssociationSetter;
use Mrself\DoctrineEntity\EntityInterface;
use Mrself\DoctrineEntity\EntityTrait;
use Doctrine\Common\Collections\ArrayCollection;

class MockEntity implements EntityInterface
{
    use EntityTrait;

    var $relativeItems;

    function __construct()
    {
        $this->relativeItems = new ArrayCollection();
    }

    public static function getClassHelperOptions()
    {
        return [
            'type' => 'entity'
        ];
    }

    function setRelativeItems($values)
    {
        AssociationSetter::autoRun();
    }

    function getRelativeItems()
    {
        return $this->relativeItems;
    }
}