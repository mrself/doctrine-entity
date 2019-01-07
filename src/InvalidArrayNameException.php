<?php declare(strict_types=1);

namespace Mrself\DoctrineEntity;

class InvalidArrayNameException extends EntityException
{
    /**
     * @var string
     */
    protected $name;

    public function __construct(string $name)
    {
        $message = "There is no method for setting a property by the name: '$name'";
        parent::__construct($message);
    }

    public function getName(): string
    {
        return $this->name;
    }
}