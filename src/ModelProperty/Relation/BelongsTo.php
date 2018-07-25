<?php
namespace Levaral\Core\ModelProperty\Relation;

class BelongsTo extends AbstractRelation
{
    /**
     * @var string
     */
    protected $property;

    /**
     * @var string
     */
    protected $relatedProperty;

    /**
     * @var string
     */
    private $relatedName;

    public function __construct($modelClass, $name, $property, $relatedName = null, $relatedProperty = null)
    {
        $this->modelClass = $modelClass;
        $this->name = $name;
        $this->property = $property;
        $this->relatedName = $relatedName;
        $this->relatedProperty = $relatedProperty ?: 'id';
    }

    public static function create($modelClass, $name, $property, $relatedName = null, $relatedProperty = null)
    {
        return new static($modelClass, $name, $property, $relatedName, $relatedProperty);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function getRelatedProperty(): string
    {
        return $this->relatedProperty;
    }

    /**
     * @return string
     */
    public function getRelatedName()
    {
        return $this->relatedName;
    }
}
