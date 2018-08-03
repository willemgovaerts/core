<?php

namespace Levaral\Core\Commands;

use Levaral\Core\ModelProperty\ModelManager;
use gossi\codegen\generator\CodeFileGenerator;
use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use gossi\codegen\model\PhpProperty;
use gossi\docblock\Docblock;
use gossi\docblock\tags\TagFactory;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;

class GenerateModelBase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'levaral:generate-model-base';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Base models.';

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach ($this->models() as $model) {
            list ($namespace, $class) = $this->getModelInfo($model);
            $this->makeBaseModel($namespace, $class, $model);
        }

        foreach (app()->make(ModelManager::class)->getModelMetas() as $key => $model) {
            $this->handleModel($key, $model);
        }

        $this->info("Base models generated successfully.");
    }

    private function getModelInfo($model)
    {
        $parts = explode('\\', ltrim($model, '\\'));

        $namespace = implode('\\', array_slice($parts, 0, -1));
        $class = array_last($parts);

        return [$namespace, $class];
    }

    protected function handleModel($key, $model)
    {
        list ($namespace, $class) = $this->getModelInfo($key);

        // Make Base Class
        $this->makeBaseArrayIteratorClass($namespace, $class);
        $this->makeBaseCollectionClass($namespace, $class);
        $this->makeBaseQueryClass($namespace, $class);
        $this->makeBaseModel($namespace, $class, $model);

        // Make Domain Class
        // $this->makeModel($namespace, $class);
        $this->makeModelQuery($namespace, $class);
    }

    protected function baseNamespace($namespace)
    {
        return str_replace('\\Domain\\', '\\Domain\\Base\\', $namespace);
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     * @return PhpMethod
     */
    protected function method(\ReflectionMethod $reflectionMethod)
    {
        $parameters = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $bodyArguments[] = '$' . $parameter->getName();
            $newParameter = PhpParameter::create($parameter->getName());
            if ($parameter->hasType()) {
                $type = $parameter->getType();
                if (ends_with($type, 'Builder')) {
                    $type = 'Builder';
                }
                $newParameter->setType((string)$type);
            }
            if ($parameter->isDefaultValueAvailable()) {
                $value = $parameter->getDefaultValue();
                if (is_array($value)) {
                    $values = array_map(function ($v) {
                        return is_string($v) ? "'$v'" : $v;
                    }, $value);
                    $newParameter->setExpression('[' . implode(', ', $values) . ']');
                } else {
                    $newParameter->setValue($value);
                }
            }
            $parameters[] = $newParameter;
        }

        return PhpMethod::create($reflectionMethod->getName())
            ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
            ->setParameters($parameters);
    }

    /**
     * @param $reflectionMethod
     * @return string
     */
    protected function argumentsString(\ReflectionMethod $reflectionMethod)
    {
        $bodyArguments = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $bodyArguments[] = '$' . $parameter->getName();
        }

        return implode(', ', $bodyArguments);
    }

    protected function generateClass(PhpClass $class, $overwrite = true)
    {
        $parts = explode('\\', ltrim($class->getNamespace(), '\\'));
        $directory = app_path(implode('/', array_slice($parts, 1)));

        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $generator = new CodeFileGenerator([
            'generateScalarTypeHints' => false,
            'generateReturnTypeHints' => false,
            'generateDocblock' => false,
        ]);

        $path = $directory . '/' . $class->getName() . '.php';

        if ($this->files->exists($path) && !$overwrite) {
            return;
        }

        $this->files->put($path, $generator->generate($class));
    }

    protected function makeBaseArrayIteratorClass($namespace, $class)
    {
        $IteratorClass = new PhpClass($this->baseNamespace($namespace) . '\Base' . $class . 'ArrayIterator');

        $IteratorClass
            ->addUseStatement($namespace . '\\' . $class)
            ->setParentClassName('\ArrayIterator');

        // Make offsetGet Method
        $offsetGetDocBlock = Docblock::create()
            ->appendTag(TagFactory::create('return', $class));

        $offsetGetBody = 'return parent::offsetGet($index);';

        $offsetGetParameter = PhpParameter::create('index');

        $offsetGetMethod = PhpMethod::create('offsetGet')
            ->setDocblock($offsetGetDocBlock)
            ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
            ->setParameters([$offsetGetParameter])
            ->setBody($offsetGetBody);

        $IteratorClass->setMethod($offsetGetMethod);

        // Make current Method
        $currentDocBlock = Docblock::create()
            ->appendTag(TagFactory::create('return', $class));

        $currentBody = 'return parent::current();';

        $currentMethod = PhpMethod::create('current')
            ->setDocblock($currentDocBlock)
            ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
            ->setBody($currentBody);

        $IteratorClass->setMethod($currentMethod);

        $this->generateClass($IteratorClass);
    }

    protected function makeBaseCollectionClass($namespace, $class)
    {
        $collectionClass = new PhpClass($this->baseNamespace($namespace) . '\Base' . $class . 'Collection');

        $collectionClass
            ->addUseStatement(\Illuminate\Database\Eloquent\Collection::class)
            ->addUseStatement($namespace . '\\' . $class)
            ->setParentClassName('Collection');

        $reflectionClass = new ReflectionClass(\Illuminate\Database\Eloquent\Collection::class);
        $override = ['get', 'offsetGet', 'find', 'getIterator', 'first', 'firstWhere', 'last'];

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if (!in_array($reflectionMethod->getName(), $override)) {
                continue;
            }

            $return = $class;
            $argumentsString = $this->argumentsString($reflectionMethod);
            $body = 'return parent::' . $reflectionMethod->getName() . '(' . $argumentsString . ');';

            if ($reflectionMethod->getName() == 'getIterator') {
                $return = 'Base' . $class . 'ArrayIterator';
                $body = 'return new Base' . $class . 'ArrayIterator($this->items);';
            }

            $docBlock = Docblock::create()
                ->appendTag(TagFactory::create('return', $return));

            $method = $this->method($reflectionMethod)
                ->setDocblock($docBlock)
                ->setBody($body);

            $collectionClass->setMethod($method);
        }

        $this->generateClass($collectionClass);
    }

    protected function makeBaseQueryClass($namespace, $class)
    {
        $baseQueryClass = new PhpClass($this->baseNamespace($namespace) . '\Base' . $class . 'Query');

        $baseQueryClass
            ->addUseStatement(\Illuminate\Database\Eloquent\Builder::class, 'EloquentBuilder')
            ->addUseStatement(\Closure::class)
            ->addUseStatement($namespace . '\\' . $class)
            ->setParentClassName('EloquentBuilder');

        $reflectionClass = new ReflectionClass(\Illuminate\Database\Eloquent\Builder::class);
        $passthru = array_merge(
            $reflectionClass->getDefaultProperties()['passthru'],
            ['average', 'aggregate', 'numericAggregate']
        );
        $passthru = array_combine($passthru, $passthru);

        $existingMethods = [];
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $existingMethods[$reflectionMethod->getName()] = $reflectionMethod->getName();
            if (starts_with($reflectionMethod->getName(), ['first', 'find'])
                || $reflectionMethod->getName() == 'get') {
                $return = $class;

                switch ($reflectionMethod->getName()) {
                    case 'findMany':
                    case 'get':
                        $return = 'Base' . $class . 'Collection';
                        break;
                    case 'first':
                    case 'find':
                        $return .= '|null';
                        break;
                }
                $docBlock = Docblock::create()
                    ->appendTag(TagFactory::create('return', $return));

                $argumentsString = $this->argumentsString($reflectionMethod);
                $body = 'return parent::' . $reflectionMethod->getName() . '(' . $argumentsString . ');';

                $method = $this->method($reflectionMethod)
                    ->setDocblock($docBlock)
                    ->setBody($body);

                $baseQueryClass->setMethod($method);
            }
        }

        $reflectionClass = new ReflectionClass(\Illuminate\Database\Query\Builder::class);

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if (isset($existingMethods[$reflectionMethod->getName()])
                || !$reflectionMethod->isPublic()
                || starts_with($reflectionMethod->getName(), '__')
                || in_array($reflectionMethod->getName(), ['hasMacro', 'macro', 'macroCall'])
            ) {
                continue;
            }

            $method = $this->method($reflectionMethod);

            $return = null;
            $argumentsString = $this->argumentsString($reflectionMethod);

            $body = '->' . $reflectionMethod->getName() . '(' . $argumentsString . ');';
            if (isset($passthru[$reflectionMethod->getName()])) {
                $body = 'return $this->toBase()' . $body;
            } else {
                $return = 'static';
                $body = '$this->query' . $body . "\n" . 'return $this;';
            }

            if ($return) {
                $docBlock = Docblock::create()
                    ->appendTag(TagFactory::create('return', $return));
                $method->setDocblock($docBlock);
            }

            $method->setBody($body);

            $baseQueryClass->setMethod($method);
        }

        $this->generateClass($baseQueryClass);
    }

    protected function makeBaseModel($namespace, $class, $model)
    {
        $castsString = "[ \r\n";
        $datesString = "[ \r\n";

        $baseModel = new PhpClass($this->baseNamespace($namespace) . '\Base' . $class);

        if ($class === 'User') {
            $baseModel
                ->addUseStatement(\Illuminate\Foundation\Auth\User::class, 'Authenticatable')
                ->addUseStatement(\Illuminate\Notifications\Notifiable::class)
                ->addTrait('Notifiable')
                ->setParentClassName('Authenticatable');
        } else {
            $baseModel
                ->addUseStatement(\Illuminate\Database\Eloquent\Model::class)
                ->setParentClassName('Model');
        }

        // Make function mutateAttribute
        $baseModel->addUseStatement('Illuminate\Support\Str');
        $mutatorBody = "return \$this->{'get'.Str::studly(\$key)}(\$value);";
        $mutateParameter[]= PhpParameter::create('key');
        $mutateParameter[] = PhpParameter::create('value');

        $mutatorMethod = PhpMethod::create('mutateAttribute')
            ->setVisibility(PhpMethod::VISIBILITY_PROTECTED)
            ->setParameters($mutateParameter)
            ->setBody($mutatorBody);

        $baseModel->setMethod($mutatorMethod);

        // Make function newEloquentBuilder
        $queryClass = $class . 'Query';
        $baseModel
            ->addUseStatement($namespace . '\\' . $queryClass);

        $eloquentBuilderDocBlock = Docblock::create()
            ->appendTag(TagFactory::create('return', $queryClass));

        $eloquentBuilderBody = 'return new ' . $queryClass . '($query);';

        $eloquentBuilderParameter = PhpParameter::create('query');

        $eloquentBuilderMethod = PhpMethod::create('newEloquentBuilder')
            ->setDocblock($eloquentBuilderDocBlock)
            ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
            ->setParameters([$eloquentBuilderParameter])
            ->setBody($eloquentBuilderBody);

        $baseModel->setMethod($eloquentBuilderMethod);

        // Make static function query
        $staticQueryDocBlock = Docblock::create()
            ->appendTag(TagFactory::create('return', $queryClass));

        $staticQueryBody = 'return parent::query();';

        $staticQueryMethod = PhpMethod::create('query')
            ->setDocblock($staticQueryDocBlock)
            ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
            ->setStatic(true)
            ->setBody($staticQueryBody);

        $baseModel->setMethod($staticQueryMethod);

        // Make new Eloquent Collection instance
        $collectionClass = 'Base' . $class . 'Collection';

        $collectionDocBlock = Docblock::create()
            ->setShortDescription('Create a new Eloquent Collection instance.')
            ->appendTag(TagFactory::create('param', 'array $models'))
            ->appendTag(TagFactory::create('return', $collectionClass));

        $collectionBody = 'return new ' . $collectionClass . '($models);';

        $collectionParameter = PhpParameter::create('models')
            ->setType('array')
            ->setExpression('[]');

        $collectionMethod = PhpMethod::create('newCollection')
            ->setDocblock($collectionDocBlock)
            ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
            ->setParameters([$collectionParameter])
            ->setBody($collectionBody);

        $baseModel->setMethod($collectionMethod);

        if (!is_string($model)) {
            if (empty($model->getProperties())) {
                try {
                    $userColumns = \DB::select('DESCRIBE ' . snake_case(str_plural($class)));
                } catch (\Exception $e) {
                    $userColumns = [];
                }
            } else {
                $userColumns = $model->getProperties();
            }

            foreach ($userColumns as $userColumn) {
                if (!isset($autoColumns[$userColumn->getName()])) {
                    // Make getter
                    $columnType = preg_replace('/^([^(]*).*$/', '$1', $userColumn->getType());
                    $returnType = $userColumn->getCastType();

                    $getterDocBlock = Docblock::create()
                        ->appendTag(TagFactory::create('return', $returnType));

                    $getterBody = "return \$this->{$userColumn->getName()};";

                    $methodName = camel_case('get_' . $userColumn->getName());

                    $getterMethod = PhpMethod::create($methodName)
                        ->setDocblock($getterDocBlock)
                        ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
                        ->setBody($getterBody);

                    $baseModel->setMethod($getterMethod);

                    // Make setter
                    $setterDocBlock = Docblock::create()
                        ->appendTag(TagFactory::create('param', $returnType . ' $value'))
                        ->appendTag(TagFactory::create('return', 'void'));

                    $setterBody = '$this->' . $userColumn->getName() . ' = $value;';

                    $methodName = camel_case('set_' . $userColumn->getName());

                    $setterParameter = PhpParameter::create('value');

                    $setterMethod = PhpMethod::create($methodName)
                        ->setDocblock($setterDocBlock)
                        ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
                        ->setParameters([$setterParameter])
                        ->setBody($setterBody);

                    $baseModel->setMethod($setterMethod);

                    if ($returnType !== '\Carbon\Carbon') {
                        $castsString .= "\t'" . $userColumn->getName() . "' => '" . $returnType . "',\r\n";
                    } else {
                        $datesString .= "\t'" . $userColumn->getName() . "',\r\n";
                    }
                }
            }

            // Define casts
            $castsString .= ']';
            $datesString .= ']';

            $castsDocBlock = Docblock::create()
                ->setShortDescription('The attributes that should be cast to native types.')
                ->appendTag(TagFactory::create('var', 'array'));

            $castsProperty = PhpProperty::create('casts')
                ->setDocblock($castsDocBlock)
                ->setVisibility(PhpMethod::VISIBILITY_PROTECTED)
                ->setExpression($castsString);

            $baseModel->setProperty($castsProperty);

            // Define dates
            $datesDocBlock = Docblock::create()
                ->setShortDescription('The attributes that should be mutated to dates.')
                ->appendTag(TagFactory::create('var', 'array'));

            $datesProperty = PhpProperty::create('dates')
                ->setDocblock($datesDocBlock)
                ->setVisibility(PhpMethod::VISIBILITY_PROTECTED)
                ->setExpression($datesString);

            $baseModel->setProperty($datesProperty);

            //Define Relationships
            if (!empty($model->getRelations())) {
                $this->generateRelation($baseModel, $class, $model);
            }
        }
        // Generate class
        $this->generateClass($baseModel);
    }

    protected function generateRelation(PhpClass $baseModel, $className, $model)
    {
        $tableName = snake_case(str_plural($className));

        $relations = $model->getRelations();

        foreach ($relations as $key=> $relation) {
            //TODO: use this condition
/*            $tableForeignColumns = $tableForeignKey->getForeignColumns();
            if (count($tableForeignColumns) !== 1) {
                continue;
            }*/

            // Make function belongsTo
            $relationNameSpace = $relation->getModelClass();
            $relationClass = class_basename($relationNameSpace);

            $baseModel
                ->addUseStatement($relationNameSpace);

            $relationDocBlock = Docblock::create()
                ->appendTag(TagFactory::create('return', '\Illuminate\Database\Eloquent\Relations\\' . class_basename($relation)));

            $relationBody = "return \$this->" . lcfirst(class_basename($relation)) . "({$relationClass}::class);";

            $params = [];
            if(lcfirst(class_basename($relation)) === 'belongsTo') {
                if($relation->getProperty()){
                    array_push($params, '"'.$relation->getProperty().'"');
                }
            } else if(lcfirst(class_basename($relation)) === 'belongsToMany') {
                if($relation->getTable()){
                    array_push($params, '"'.$relation->getTable().'"');
                }
                if($relation->getProperty()){
                    array_push($params, '"'.$relation->getProperty().'"');
                }
            }
            if(count($params)){
                $relationBody = "return \$this->" . lcfirst(class_basename($relation)) . "({$relationClass}::class,"
                .implode(',', $params).");";
            }

            $relationMethod = PhpMethod::create($relation->getName())
                ->setDocblock($relationDocBlock)
                ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
                ->setBody($relationBody);

            $baseModel->setMethod($relationMethod);
        }
    }

    protected function makeModelQuery($namespace, $class)
    {
        $modelQuery = new PhpClass($namespace . '\\' . $class . 'Query');
        $modelQuery
            ->addUseStatement($this->baseNamespace($namespace) . '\Base' . $class . 'Query')
            ->setParentClassName('Base' . $class . 'Query');

        // Generate class
        $this->generateClass($modelQuery, false);
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
}
