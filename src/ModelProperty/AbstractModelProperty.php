<?php

namespace Levaral\Core\ModelProperty;


class AbstractModelProperty
{
    protected $name = null;
    protected $type = null;
    protected $unique = false;
    protected $nullable = false;
    protected $rules = [];
    protected $default = null;
    protected $description = null;
    protected $castType = 'string';

    public function __construct($name)
    {
        $this->name = $name;
    }

    public static function create($name)
    {
        return new static($name);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getRenderProperty()
    {
        if (!$this->type) {
            throw new \Exception('Field type is not defined');
        }

        if (!$this->name) {
            throw new \Exception('Field name is not defined');
        }

        $props = [
            'name' => $this->name,
            'type' => $this->type,
        ];

        $props += ($this->unique) ? ['unique' => $this->unique] : $props;
        $props += ($this->nullable) ? ['nullable' => $this->nullable] : $props;
        $props += ($this->description) ? ['description' => $this->description] : $props;
        $props += (!is_null($this->default)) ? ['default' => $this->default] : $props;

        return $props;
    }

    public function rules(array $rules)
    {
        $this->rules = $rules;
        return $this;
    }

    public function getDefaultRules()
    {
        $rules = [];

        $rules = (!$this->nullable) ? ['required'] : $rules;
        $rules = (isset($this->length) && $this->length) ? array_merge($rules, ['max:'.$this->length]) : $rules;

        return $rules;
    }

    public function getPropertyRules()
    {
        return [];
    }

    public function getRules(): array
    {
        return array_merge($this->getDefaultRules(), $this->getPropertyRules(), $this->rules);
    }

    public function getType()
    {
        return $this->type;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function unique(bool $unique = true): self
    {
        $this->unique = $unique;
        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function nullable($nullable = true): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function default($default): self
    {
        $this->default = $default;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function description($description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function name($name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCastType()
    {
        return $this->castType;
    }
}