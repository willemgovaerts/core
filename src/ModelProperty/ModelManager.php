<?php

namespace Levaral\Core\ModelProperty;
use Levaral\Core\ModelProperty\Relation\BelongsTo;
use Levaral\Core\ModelProperty\Relation\BelongsToMany;
use Levaral\Core\ModelProperty\Relation\HasMany;
use Levaral\Core\ModelProperty\Relation\HasOne;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class ModelManager
{
    protected $models = [];
    protected $modelMetas = [];

    public function __construct()
    {
        $this->init();
    }

    public function getModels(): array
    {
        return $this->models;
    }

    public function getModelMetas(): array
    {
        return $this->modelMetas;
    }

    public function getModelMeta($model): ModelMeta
    {
        return array_get($this->modelMetas, $model);
    }

    protected function init()
    {
        $this->models = $this->models();
        $this->modelMetas = $this->modelsMeta();
    }

    protected function models(): array
    {
        $fileSystem = new Filesystem();
        $files = $fileSystem->exists(app_path('Domain')) ? $fileSystem->allFiles(app_path('Domain')) : [];

        $models = [];

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $namespace = str_replace('/', '\\', '\\App' . str_replace(app_path(), '', $file->getPath()));
            $class = substr($file->getFilename(), 0, -4);

            if (substr($class, -5) == 'Query') {
                continue;
            }

            // cannot use reflection because some classes might not be defined yet, resulting in a fatal error
            preg_match("/\s*class\s*$class\s*extends\sBase$class\s*\{\s*/", $file->getContents(), $matches);
            if ($matches) {
                $models[] = ltrim($namespace.'\\'.$class, '\\');
            }
        }

        return $models;
    }

    protected function modelsMeta(): array
    {
        $modelMetas = [];
        $propertiesByModel = [];
        $propertyGroupsByModel = [];
        $rulesByModel = [];
        $relationsByModel = [];
        $titleField = [];

        foreach ($this->getModels() as $model) {
            $relationsByModel[$model] = [];
        }

        foreach ($this->getModels() as $model) {
            list($properties, $groups) = $this->getPropertiesByModel($model);
            $propertiesByModel[$model] = $properties;
            $propertyGroupsByModel[$model] = $groups;
            $titleField[$model] = self::getTitleFieldByModel($model);
            $rulesByModel[$model] = $this->getRulesByModel($model, $properties);
            $relations = $this->getRelationsByModel($model);
            $relationsByModel[$model] = array_merge($relationsByModel[$model], $relations);
            /** @var \Levaral\Core\ModelProperty\Relation\AbstractRelation $relation */
            foreach ($relations as $relation) {
                if ($relation instanceof BelongsTo) {
                    /** @var AbstractModelProperty $property */
                    $property = $properties[$relation->getProperty()];
                    $relationClass = HasMany::class;
                    $defaultRelatedName = str_plural(snake_case(class_basename($model)));

                    if ($property->isUnique()) {
                        $relationClass = HasOne::class;
                        $defaultRelatedName = snake_case(class_basename($model));
                    }

                    $relationsByModel[$relation->getModelClass()][] = new $relationClass(
                        $model,
                        $relation->getRelatedName() ?: $defaultRelatedName,
                        $relation->getRelatedProperty(),
                        $relation->getProperty()
                    );
                }

                if ($relation instanceof BelongsToMany) {
                    $defaultRelatedName = str_plural(snake_case(class_basename($model)));
                    /** @var AbstractModelProperty $property */
                    $relationsByModel[$relation->getModelClass()][] = new BelongsToMany(
                        $model,
                        $relation->getRelatedName() ?: $defaultRelatedName,
                        $relation->getTable(),
                        $relation->getRelatedPropertyName()
                    );
                }
            }
        }

        foreach ($this->getModels() as $model) {
            $modelMetas[$model] = new ModelMeta(
                $propertiesByModel[$model],
                $rulesByModel[$model],
                $relationsByModel[$model],
                $propertyGroupsByModel[$model],
                $titleField[$model]
            );
        }

        return $modelMetas;
    }

    public static function getTitleFieldByModel($model)
    {
        if (method_exists($model, 'getTitleField')) {
            return $model->getTitleField();
        } elseif (method_exists($model, 'getTitle')) {
            return 'title';
        } elseif (method_exists($model, 'getName')) {
            return 'name';
        }

        return null;
    }

    /**
     * @param $model
     * @return array
     */
    protected function getPropertiesByModel($model): array
    {
        $properties = [];
        $groups = [];

        $entity = new $model();

        $properties['id'] = IntegerProperty::create('id')->increments();

        if (method_exists($entity, 'properties')) {
            // TODO shouldn't we throw an exception if the properties method is missing?
            foreach ($entity->properties() as $groupName => $propertyOrGroup) {
                if (is_array($propertyOrGroup)) {
                    $group = [];
                    foreach ($propertyOrGroup as $property) {
                        /** @var AbstractModelProperty $property */
                        $properties[$property->getName()] = $property;
                        $group[] = $property->getName();
                    }
                    $groups[$groupName] = $group;
                } else {
                    /** @var AbstractModelProperty $propertyOrGroup */
                    $properties[$propertyOrGroup->getName()] = $propertyOrGroup;
                    if (!isset($groups['general'])) {
                        $groups['general'] = [];
                    }
                    $groups['general'][] = $propertyOrGroup->getName();
                }
            }
        }

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($entity))) {
            $properties['deleted_at'] = DateTimeProperty::create('deleted_at')->nullable();
        }

        if ($entity->timestamps) {
            $properties['created_at'] = DateTimeProperty::create('created_at')->nullable();
            $properties['updated_at'] = DateTimeProperty::create('updated_at')->nullable();
        }

        return [$properties, $groups];
    }

    /**
     * @param $model
     * @param array $properties
     * @return array
     */
    protected function getRulesByModel($model, array $properties): array
    {
        $entity = new $model();
        $rules = (method_exists($entity, 'rules')) ? $entity->rules() : [];

        /** @var AbstractModelProperty $property */
        foreach ($properties as $property) {
            $defaultRules = array_get($rules, $property->getName(), []);
            $defaultRules = is_array($defaultRules) ? $defaultRules : [$defaultRules];
            $rules[$property->getName()] = array_unique(array_merge($defaultRules, $property->getRules()));
        }

        return $rules;
    }

    protected function getRelationsByModel($model)
    {
        $entity = new $model();
        $relations = [];

        if (method_exists($entity, 'relations')) {
            $relations = $entity->relations();
        }

        return $relations;
    }
}