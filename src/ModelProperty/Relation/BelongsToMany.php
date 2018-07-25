<?php
namespace Levaral\Core\ModelProperty\Relation;

class BelongsToMany extends AbstractRelation
{
    public $relatedPropertyName;
    public $relatedName;
    public $table;
    public $property;

    public function __construct($modelClass, $name, $table = null, $property = null, $relatedName = null, $relatedPropertyName = null)
    {
        $this->modelClass = $modelClass;
        $this->name = $name;
        $this->relatedPropertyName = $relatedPropertyName;
        $this->relatedName = $relatedName;
        $this->table = $table;
        $this->property = $property;
    }

    public static function create($modelClass, $name, $table = null, $property = null, $relatedName = null, $relatedPropertyName = null)
    {
        return new static($modelClass, $name, $table, $property, $relatedName, $relatedPropertyName);
    }

    public function getRelatedPropertyName()
    {
        return $this->relatedPropertyName;
    }

    public function getRelatedName()
    {
        return $this->relatedName;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getProperty()
    {
        return $this->property;
    }
}
