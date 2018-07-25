<?php

namespace Levaral\Core\ModelProperty\Relation;


class AbstractRelation
{
    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}