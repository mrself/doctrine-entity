<?php declare(strict_types=1);

namespace Mrself\DoctrineEntity\Tests\Unit\Entity\EntityTrait;

use Mrself\DoctrineEntity\EntityInterface;
use Mrself\DoctrineEntity\EntityTrait;
use PHPUnit\Framework\TestCase;

class ToArrayTest extends TestCase
{
    public function testItReturnsMapOfPropertiesToValues()
    {
        $entity = new class implements EntityInterface {
            use EntityTrait;

            protected $field1 = 'value1';

            public function __construct()
            {
                $this->entityConstruct();
                $this->id = 1;
            }

            function getField1()
            {
                return $this->field1;
            }
        };
        $expected = ['field1' => 'value1', 'id' => 1];
        $this->assertEquals($expected, $entity->toArray());
    }

    public function testItIgnoresAttributes()
    {
        $entity = new class implements EntityInterface {
            use EntityTrait;

            protected $field1 = 'value1';

            public function __construct()
            {
                $this->entityConstruct();
                $this->serializerIgnoredAttributes = ['id'];
            }

            function getField1()
            {
                return $this->field1;
            }
        };
        $expected = ['field1' => 'value1'];
        $this->assertEquals($expected, $entity->toArray());
    }

    public function testItHandlesCircleRefs()
    {
        $entity1 = new class implements EntityInterface {
            use EntityTrait;

            protected $field1 = 'value1';

            public $entity2;

            public function __construct()
            {
                $this->entityConstruct();
                $this->serializerIgnoredAttributes = ['id'];
                $this->id = 1;
            }

            function getField1()
            {
                return $this->field1;
            }

            public function getEntity2()
            {
                return $this->entity2;
            }
        };

        $entity2 = new class implements EntityInterface {
            protected $field2 = 'value2';

            public $entity1;

            function getField2()
            {
                return $this->field2;
            }
        };
        $entity2->entity1 = $entity1;
        $entity1->entity2 = $entity2;
        $expected = ['field1' => 'value1', 'entity2' => $entity2];
        $this->assertEquals($expected, $entity1->toArray());
    }

    public function testItWorksWithDates()
    {
        $entity = new class implements EntityInterface {
            use EntityTrait;

            protected $date;

            function getDate()
            {
                return new \DateTime();
            }
        };

        $array = $entity->toArray();
        $this->assertCount(2, $array);
        $this->assertNull($array['id']);
        $this->assertInstanceOf(\DateTime::class, $array['date']);
    }
}