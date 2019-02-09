<?php declare(strict_types=1);

namespace Mrself\DoctrineEntity\AssociationSetter;

use Mrself\DoctrineEntity\EntityInterface;
use Mrself\DoctrineEntity\EntityTrait;
use Mrself\ClassHelper\ClassHelper;
use Doctrine\Common\Collections\Collection;

class AssociationSetter
{

    /**
     * @var EntityTrait|EntityInterface
     */
    protected $entity;

    /**
     * @var array
     */
    protected $associations;

    /**
     * Existing entity association collection
     * @var Collection
     */
    protected $collection;

    /**
     * Name of inverse association
     * @var string
     */
    protected $inverseName;

    /**
     * @var string
     */
    protected $associationName;

    /**
     * Runs setter with specific parameters
     * @param EntityInterface $entity
     * @param array $associations
     * @param string $inverseName
     * @param string $associationName
     */
    public static function runWith(EntityInterface $entity, array $associations, string $inverseName, string $associationName)
    {
        $self = new static();
        $self->entity = $entity;
        $self->associations = $associations;
        $self->inverseName = ucfirst($inverseName);
        $self->associationName = $associationName;
        $self->run();
    }

	/**
	 * Runs setting
	 */
    protected function run()
    {
        $this->defineCollection();
        array_walk($this->associations, [$this, 'setSingle']);
        $this->removeUnnecessaryAssociations();
    }

	/**
	 * Defines associations collection property of entity
	 */
    protected function defineCollection()
    {
        $methodGet = 'get' . ucfirst($this->associationName);
        $this->collection = $this->entity->$methodGet();
    }

	/**
	 * Removes associations from existing collection which are not in
	 * new association values
	 */
    protected function removeUnnecessaryAssociations()
    {
        foreach ($this->collection as $item) {
            if (!in_array($item, $this->associations)) {
                $this->collection->removeElement($item);
            }
        }
    }

	/**
	 * Sets / adds association to existing entity property
	 * @param * $association
	 * @throws \Mrself\DoctrineEntity\AssociationSetter\InvalidAssociationException
	 */
    protected function setSingle($association)
    {
        if ($this->collection->contains($association)) {
            return;
        }

        $this->collection->add($association);
        $association->{$this->getInverseMethod($association)}($this->entity);
    }

	/**
	 * Returns method to call on association (inverse side) to set current
	 * entity
	 * @param * $association
	 * @return string
	 * @throws InvalidAssociationException
	 */
    protected function getInverseMethod($association): string
    {
        $inverseName = $this->inverseName;
        if (method_exists($association, 'set' . $inverseName)) {
            return'set' . $inverseName;
        }
        if (method_exists($association, 'add' . $inverseName)) {
            return 'add' . $inverseName;
        }
        throw new InvalidAssociationException($this->associationName, $inverseName);
    }

	/**
	 * Returns inverse association name
	 * @return string
	 */
    protected function getInverseName(): string
    {
        if ($this->inverseName) {
            return $this->inverseName;
        }

        return ClassHelper::make($this->entity)->getName();
    }

}