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
     * @var array
     */
    protected $callerTrace;

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
	 * Runs setter with specific parameters
	 * @param EntityInterface $entity
	 * @param array $associations
	 * @param string $inverseName
	 */
    public static function runWith(EntityInterface $entity, array $associations, string $inverseName = null)
    {
        $self = new static();
        $self->entity = $entity;
        $self->associations = $associations;
        $self->callerTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
        $self->inverseName = $inverseName;
        $self->run();
    }

	/**
	 * Sets associations when is called from entity method.
	 * Auto defines needed parameters
	 * ```
	 * new class {
	 * 	setMyAssociations($values)
	 * 	{
	 * 		// This will set `myAssociation` collection
	 * 		AssociationSetter::autoRun();
	 * 	}
	 * ```
	 * }
	 * @param string $inverseName
	 */
    public static function autoRun(string $inverseName = null)
    {
        $self = new static();
        $self->callerTrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1];
        $self->entity = $self->callerTrace['object'];
        $self->associations = $self->callerTrace['args'][0];
        $self->inverseName = $inverseName;
        $self->run();
    }

	protected function formatAssociations()
	{
		$name = $this->getAssociationName();
		$associationClass = $this->entity
            ->getEntityOptions()['association']['classes'][$name];
		$this->associations = array_map(function($association) use ($associationClass) {
			if (is_array($association)) {
				return call_user_func([$associationClass, 'sfromArray'], $association);
			}
			if ($association instanceof $associationClass) {
				return $association;
			}
			throw new \Exception();
		}, $this->associations);
    }

	/**
	 * Runs setting
	 * @throws InvalidCallerException
	 */
    public function run()
    {
        $this->ensureValidCaller();
        $this->defineCollection();
        array_walk($this->associations, [$this, 'setSingle']);
        $this->removeUnnecessaryAssociations();
    }

	/**
	 * Defines associations collection property of entity
	 */
    protected function defineCollection()
    {
        $associationName = $this->getAssociationName();
        $methodGet = 'get' . ucfirst($associationName);
        $this->collection = $this->entity->$methodGet();
    }

	/**
	 * Ensures that a caller is entity which implements EntityInterface
	 * @throws InvalidCallerException
	 */
    protected function ensureValidCaller()
    {
        $isCallerEntity = is_a(
            $this->callerTrace['class'],
            EntityInterface::class,
            true
        );
        if (!$isCallerEntity) {
            throw new InvalidCallerException($this->callerTrace['class']);
        }
    }

	/**
	 * Retrives association name from caller method name
	 * @return string
	 */
    protected function getAssociationName()
    {
        $associationMethod = $this->callerTrace['function'];
        $associationName = str_replace('set', '', $associationMethod);
        $associationName = lcfirst($associationName);
        return $associationName;
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
        $inverseName = $this->getInverseName();
        if (method_exists($association, 'set' . $inverseName)) {
            return'set' . $inverseName;
        }
        if (method_exists($association, 'add' . $inverseName)) {
            return 'add' . $inverseName;
        }
        throw new InvalidAssociationException($this->getAssociationName(), $inverseName);
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