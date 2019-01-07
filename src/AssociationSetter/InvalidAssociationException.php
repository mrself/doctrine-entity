<?php declare(strict_types=1);

namespace Mrself\DoctrineEntity\AssociationSetter;

use Mrself\DoctrineEntity\EntityException;

class InvalidAssociationException extends EntityException
{
    /**
     * Association name
     * @var string
     */
    protected $association;

    /**
     * Association inverse name
     * @var string
     */
    protected $inverseName;

    public function __construct(string $association, string $inverseName)
    {
        $this->association = $association;
        $this->inverseName = $inverseName;

        parent::__construct("Association ($association) does not contain a method for setting/adding a current entity ($inverseName)");
    }
}