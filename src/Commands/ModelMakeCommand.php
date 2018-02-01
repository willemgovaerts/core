<?php

namespace Levaral\Core\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand as Command;
use gossi\codegen\generator\CodeFileGenerator;
use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpProperty;
use gossi\docblock\Docblock;
use gossi\docblock\tags\TagFactory;

class ModelMakeCommand extends Command
{
    protected function getDefaultNamespace($rootNamespace)
    {
        return "{$rootNamespace}\Domain";
    }

    protected function baseNamespace($namespace)
    {
        return str_replace('\\Domain\\', '\\Domain\\Base\\', $namespace);
    }

    protected function buildClass($name)
    {
        // generate model class
        $parts = explode('\\', ltrim($name, '\\'));

        $namespace = implode('\\', array_slice($parts, 0, -1));
        $class = array_last($parts);

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

            $hiddenProperty = PhpProperty::create('hidden')
                ->setDocblock($hiddenDockBlock)
                ->setVisibility(PhpMethod::VISIBILITY_PROTECTED)
                ->setExpression($hiddenString);

            $model->setProperty($hiddenProperty);
        }

        // Generate class
        $generator = new CodeFileGenerator([
            'generateScalarTypeHints' => false,
            'generateReturnTypeHints' => false,
            'generateDocblock' => false,
        ]);

        return $generator->generate($model);

    }

    public function handle()
    {
        parent::handle();
        $this->call('models');
    }
}