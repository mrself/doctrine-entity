<?php declare(strict_types=1);

namespace Mrself\DoctrineEntity;

use Mrself\DoctrineEntity\AssociationSetter\AssociationSetter;
use ICanBoogie\Inflector;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

trait EntityTrait {

	protected $id;

    /**
     * @var Inflector
     */
	protected $inflector;

    /**
     * @var array
     */
	protected $serializerIgnoredAttributes = [];

	protected function entityConstruct()
    {
        $this->inflector = Inflector::get();
    }

    public function getId()
    {
        return $this->id;
	}

    /**
     * @param array $array
     * @return static
     * @throws InvalidArrayNameException
     */
    public function fromArray(array $array): self
    {
        foreach ($array as $name => $value) {
            $method = 'set' . $this->inflector
				->camelize($name, Inflector::DOWNCASE_FIRST_LETTER);
            if (!method_exists($this, $method)) {
                throw new InvalidArrayNameException($name);
            }
        }
        return $this;
	}

	public static function sfromArray(array $array): self
    {
        return (new static())->fromArray($array);
    }

    /**
     * Set associations of 'OneToMany' and "ManyToMany' relations
     * @param null|array $associations Array of associations or null
     * @return static
     */
    protected function setAssociations(array $associations = []) {
        AssociationSetter::autoRun();
        return $this;
    }

	/**
	 * Converts entity to array
	 * @return array
	 */
    public function toArray(): array
    {
        return $this->getSerializer(new JsonEncoder())->normalize($this);
    }

    protected function getNormalizer()
    {
        return (new ObjectNormalizer())
            ->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            })
            ->setIgnoredAttributes($this->getSerializerIgnoredAttributes());
    }

    protected function getSerializer($encoder)
    {
        $encoder = $encoder ?: new JsonEncoder();
        $normalizer = $this->getNormalizer();
        return new Serializer([$normalizer], [$encoder]);
    }

    protected function getSerializerIgnoredAttributes(): array
    {
        return $this->serializerIgnoredAttributes;
    }

	/**
	 * Serializes entity
	 * @param EncoderInterface $encoder
	 * @return string
	 */
    public function serialize($encoder = null)
    {
        $encoder = $encoder ?: new JsonEncoder();
        return $this->getSerializer($encoder)->serialize($this, $encoder::FORMAT);
    }

}