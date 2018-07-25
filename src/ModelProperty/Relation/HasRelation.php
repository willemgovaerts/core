<?php
namespace Levaral\Core\ModelProperty\Relation;

class HasRelation extends AbstractRelation
{
    /**
     * @var string
     */
    protected $property;

    /**
     * @var string
     */
    protected $relatedProperty;

    public function __construct($modelClass, $name, $relatedProperty, $property = null)
    {
        $this->modelClass = $modelClass;
        $this->name = $name;
        $this->relatedProperty = $relatedProperty;
        $this->property = $property ?: 'id';
    }

    public static function create($modelClass, $name, $relatedProperty, $property = null)
    {
        return new static($modelClass, $name, $relatedProperty, $property);
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
}
