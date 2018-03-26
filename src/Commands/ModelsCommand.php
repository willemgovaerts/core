<?php

namespace Levaral\Core\Commands;

use gossi\codegen\generator\CodeFileGenerator;
use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use gossi\codegen\model\PhpProperty;
use gossi\docblock\Docblock;
use gossi\docblock\tags\TagFactory;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;

class ModelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'levaral:models {model?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate all base models';

    /**
     * @var Filesystem
     */
    protected $files;

    protected $models;

    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files, DatabaseManager $databaseManager)
    {
        parent::__construct();
        $this->files = $files;
        $this->databaseManager = $databaseManager;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model = $this->argument('model');
        if ($model) {
            $parts = explode('\\', ltrim($model, '\\'));

            $namespace = implode('\\', array_slice($parts, 0, -1));
            $class = array_last($parts);
            $models = [
                [
                    'class' => $class,
                    'namespace' => $namespace,
                    'table' => snake_case(str_plural($class)),
                ]
            ];
        } else {
            $models = $this->findModels();
        }

        foreach ($models as $model) {
            $this->handleModel($model);
        }
    }

    protected function handleModel($model)
    {
        $namespace = $model['namespace'];
        $class = $model['class'];

        // Make Base Class
        $this->makeBaseArrayIteratorClass($namespace, $class);
        $this->makeBaseCollectionClass($namespace, $class);
        $this->makeBaseQueryClass($namespace, $class);
        $this->makeBaseModel($namespace, $class, $model['table']);

        // Make Domain Class
        // $this->makeModel($namespace, $class);
        $this->makeModelQuery($namespace, $class);
    }

    protected function findModels()
    {
        if (!is_null($this->models)) {
            return $this->models;
        }

        $files = $this->files->exists(app_path('Domain')) ? $this->files->allFiles(app_path('Domain')) : [];
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
                $table = snake_case(str_plural($class));
                $models[$table] = [
                    'namespace' => $namespace,
                    'class' => $class,
                    'table' => $table,
                ];
            }
        }

        $this->models = $models;

        return $models;
    }

    protected function baseNamespace($namespace)
    {
        return str_replace('\\Domain\\', '\\Domain\\Base\\', $namespace);
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

    protected function makeBaseModel($namespace, $class, $tableName)
    {
        $returnTypeMapping = [
            'int' => 'integer',
            'varchar' => 'string',
            'text' => 'string',
            'tinyint' => 'boolean',
            'timestamp' => '\Carbon\Carbon',
        ];

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

        try {
            $userColumns = \DB::select('DESCRIBE ' . $tableName);
        } catch (\Exception $e) {
            $userColumns = [];
        }
        foreach ($userColumns as $userColumn) {
            if (!isset($autoColumns[$userColumn->Field])) {

                // Make getter
                $columnType = preg_replace('/^([^(]*).*$/', '$1', $userColumn->Type);
                $returnType = isset($returnTypeMapping[$columnType]) ? $returnTypeMapping[$columnType] : 'string';

                $getterDocBlock = Docblock::create()
                    ->appendTag(TagFactory::create('return', $returnType));

                $getterBody = "return \$this->{$userColumn->Field};";

                $methodName = camel_case('get_' . $userColumn->Field);

                $getterMethod = PhpMethod::create($methodName)
                    ->setDocblock($getterDocBlock)
                    ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
                    ->setBody($getterBody);

                $baseModel->setMethod($getterMethod);

                // Make setter
                $setterDocBlock = Docblock::create()
                    ->appendTag(TagFactory::create('param', $returnType . ' $value'))
                    ->appendTag(TagFactory::create('return', 'void'));

                $setterBody = '$this->' . $userColumn->Field . ' = $value;';

                $methodName = camel_case('set_' . $userColumn->Field);

                $setterParameter = PhpParameter::create('value');

                $setterMethod = PhpMethod::create($methodName)
                    ->setDocblock($setterDocBlock)
                    ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
                    ->setParameters([$setterParameter])
                    ->setBody($setterBody);

                $baseModel->setMethod($setterMethod);

                if ($returnType !== '\Carbon\Carbon') {
                    $castsString .= "\t'" . $userColumn->Field . "' => '" . $returnType . "',\r\n";
                } else {
                    $datesString .= "\t'" . $userColumn->Field . "',\r\n";
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
        $this->generateRelation($baseModel, $class, $tableName);

        // Generate class
        $this->generateClass($baseModel);
    }

    /**
     * Not used
     */
    protected function makeModel($namespace, $class)
    {
        if (class_exists($namespace . '\\' . $class)) {
            if (!is_subclass_of($namespace . '\\' . $class, $this->baseNamespace($namespace) . '\Base' . $class)) {
                $this->comment('"' . $namespace . '\\' . $class . '" is already exist, but not extending the "' . $namespace . '\Base\\Base' . $class . '"');
            }

        } else {
            $model = new PhpClass($namespace . '\\' . $class);
            $model
                ->addUseStatement($this->baseNamespace($namespace) . '\Base' . $class)
                ->setParentClassName('Base' . $class);

            if ($class === 'User') {
                // Define hidden
                $hiddenString = "[\r\n \t'password', 'remember_token', \r\n]";
                $hiddenDockBlock = Docblock::create()
                    ->setShortDescription('The attributes that should be hidden for arrays.')
                    ->appendTag(TagFactory::create('var', 'array'));

                $hiddenProperty = PhpProperty::create('dates')
                    ->setDocblock($hiddenDockBlock)
                    ->setVisibility(PhpMethod::VISIBILITY_PROTECTED)
                    ->setExpression($hiddenString);

                $model->setProperty($hiddenProperty);
            }

            // Generate class
            $this->generateClass($model);
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

    protected function generateRelation(PhpClass $baseModel, $className, $tableName)
    {
        $schemaManager = $this->databaseManager->connection()->getDoctrineSchemaManager();
        // add belongsTo relations
        $foreignKeys = $schemaManager->listTableForeignKeys($tableName);
        foreach ($foreignKeys as $tableForeignKey) {
            $tableForeignColumns = $tableForeignKey->getForeignColumns();
            if (count($tableForeignColumns) !== 1) {
                continue;
            }

            // Make function belongsTo
            $belongsToNameSpace = $this->models[$tableForeignKey->getForeignTableName()]['namespace'];
            $belongsToClass = $this->models[$tableForeignKey->getForeignTableName()]['class'];
            $baseModel
                ->addUseStatement($belongsToNameSpace . '\\' . $belongsToClass);

            $belongsToDocBlock = Docblock::create()
                ->appendTag(TagFactory::create('return', '\Illuminate\Database\Eloquent\Relations\BelongsTo'));

            $belongsToBody = "return \$this->belongsTo({$belongsToClass}::class);";

            $belongsToMethod = PhpMethod::create(strtolower($belongsToClass))
                ->setDocblock($belongsToDocBlock)
                ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
                ->setBody($belongsToBody);

            $baseModel->setMethod($belongsToMethod);
        }
        // add belongsToMany, hasOne and hasMany relations
        $schemaManager->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $tables = $schemaManager->listTables();
        foreach ($tables as $table) {
            if ($table->getName() === $tableName) {
                continue;
            }

            $foreignKeys = $table->getForeignKeys();
            // add belongsToMany relation
            if (count($foreignKeys) === 2) {
                $keys = array_keys($foreignKeys);
                $firstForeignKey = $foreignKeys[$keys[0]];
                $secondForeignKey = $foreignKeys[$keys[1]];
                $firstForeignTable = $firstForeignKey->getForeignTableName();
                $secondForeignTable = $secondForeignKey->getForeignTableName();
                $defaultJoinTable = $this->getDefaultJoinTableName($firstForeignTable, $secondForeignTable);
                if ($defaultJoinTable === $table->getName()) {

                    foreach ($foreignKeys as $name => $foreignKey) {
                        if ($foreignKey->getForeignTableName() === $tableName) {
                            $localColumns = $foreignKey->getLocalColumns();
                            if (count($localColumns) !== 1) {
                                continue;
                            }

                            $key = array_search($name, $keys) === 0 ? 1 : 0;
                            $secondForeignKey = $foreignKeys[$keys[$key]];
                            $secondForeignTable = $secondForeignKey->getForeignTableName();

                            // Make function belongsToMany
                            $belongsToManyNameSpace = $this->models[$secondForeignTable]['namespace'];
                            $belongsToManyClass = $this->models[$secondForeignTable]['class'];
                            $baseModel
                                ->addUseStatement($belongsToManyNameSpace . '\\' . $belongsToManyClass);

                            $belongsToManyDocBlock = Docblock::create()
                                ->appendTag(TagFactory::create('return', '\Illuminate\Database\Eloquent\Relations\BelongsToMany'));

                            $belongsToManyBody = "return \$this->belongsToMany({$belongsToManyClass}::class)->withTimestamps();";

                            $belongsToManyMethod = PhpMethod::create(camel_case(str_plural($belongsToManyClass)))
                                ->setDocblock($belongsToManyDocBlock)
                                ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
                                ->setBody($belongsToManyBody);

                            $baseModel->setMethod($belongsToManyMethod);

                            break;
                        }
                    }

                    continue;
                }
            }

            // add hasOne and hasMany relation
            foreach ($foreignKeys as $name => $foreignKey) {
                if ($foreignKey->getForeignTableName() === $tableName) {
                    $localColumns = $foreignKey->getLocalColumns();
                    if (count($localColumns) !== 1) {
                        continue;
                    }

                    $foreignColumn = $localColumns[0];

                    $hasNameSpace = $this->models[$foreignKey->getLocalTableName()]['namespace'];
                    $hasClass = $this->models[$foreignKey->getLocalTableName()]['class'];
                    $baseModel
                        ->addUseStatement($hasNameSpace . '\\' . $hasClass);

                    // Make function hasMany
                    $hasDocBlock = Docblock::create()
                        ->appendTag(TagFactory::create('return', '\Illuminate\Database\Eloquent\Relations\HasMany'));

                    $hasBody = "return \$this->hasMany({$hasClass}::class);";

                    $hasMethodName = camel_case(str_plural($hasClass));

                    if ($this->isColumnUnique($table, $foreignColumn)) {
                        // Make function hasOne to overwrite hasMany
                        $hasDocBlock = Docblock::create()
                            ->appendTag(TagFactory::create('return', '\Illuminate\Database\Eloquent\Relations\HasOne'));

                        $hasBody = "return \$this->hasOne({$hasClass}::class);";

                        $hasMethodName = strtolower($hasClass);
                    }

                    $hasMethod = PhpMethod::create($hasMethodName)
                        ->setDocblock($hasDocBlock)
                        ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
                        ->setBody($hasBody);

                    $baseModel->setMethod($hasMethod);
                }
            }
        }
    }

    /**
     * @param Table $table
     * @param string $column
     * @return bool
     */
    protected function isColumnUnique(Table $table, $column)
    {
        foreach ($table->getIndexes() as $index) {
            $indexColumns = $index->getColumns();
            if (count($indexColumns) !== 1) {
                continue;
            }
            $indexColumn = $indexColumns[0];
            if ($indexColumn === $column && $index->isUnique()) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param string $tableOne
     * @param string $tableTwo
     * @return string
     */
    public function getDefaultJoinTableName($tableOne, $tableTwo)
    {
        $tables = [str_singular($tableOne), str_singular($tableTwo)];
        sort($tables);

        return implode('_', $tables);
    }
}
