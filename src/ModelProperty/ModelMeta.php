<?php

namespace Levaral\Core\ModelProperty;


class ModelMeta
{
    protected $properties;
    protected $rules;
    protected $relations;
    protected $propertyGroups;
    protected $titleField;

    public function __construct(array $properties = [], array $rules = [], array $relations = [], array $propertyGroups = [], $titleField = null)
    {
        $this->properties = $properties;
        $this->rules = $rules;
        $this->relations = $relations;
        $this->propertyGroups = $propertyGroups;
        $this->titleField = $titleField;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function getRelations()
    {
        return $this->relations;
    }

    public function getPropertyGroups()
    {
        return $this->propertyGroups;
    }

    public function getRelationByModel($model)
    {
        $relation = null;

        foreach ($this->getRelations() as $relation) {
            if ($relation->getModelClass() == $model) {
                return $relation;
            }
        }

        return $relation;
    }

    public function getRelationByName($name)
    {
        $relation = null;

        foreach ($this->getRelations() as $relation) {
            if ($relation->getName() == $name) {
                return $relation;
            }
        }

        return $relation;
    }

    public function getRelationMethods()
    {
        $methods = null;

        foreach ($this->getRelations() as $relation) {
            $methods[] = $relation->getName();
        }

        return $methods;
    }

    public function getTitleField()
    {
        return $this->titleField;
    }
}