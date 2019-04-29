<?php declare(strict_types=1);

namespace Mrself\DoctrineEntity\Tests\Unit\Entity\EntityTrait;

use Mrself\DoctrineEntity\EntityTrait;
use PHPUnit\Framework\TestCase;

class OnlyTest extends TestCase
{
    public function testWithNonAssociativeArrayKeys()
    {
        $entity = new class {
            use EntityTrait;

            protected $field;

            public function getField()
            {
                return 'value';
            }
        };
        $fields = $entity->only(['field']);
        $this->assertEquals('value', $fields['field']);
    }

    public function testWithAssociativeArrayKeys()
    {
        $entity = new class {
            use EntityTrait;

            protected $field;

            public function getField()
            {
                return 'value';
            }
        };
        $fields = $entity->only(['field1' => 'field']);
        $this->assertEquals('value', $fields['field1']);
    }
}