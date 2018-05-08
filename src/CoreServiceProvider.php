<?php

namespace Levaral\Core;

use Levaral\Core\Commands\CreateMailLogsTable;
use Levaral\Core\Commands\CreateMailTemplateTables;
use Levaral\Core\Commands\CreateUserExpoTokensTable;
use Levaral\Core\Commands\GenerateBrowserTest;
use Levaral\Core\Commands\GenerateLanguageJson;
use Levaral\Core\Commands\GenerateStructure;
use Levaral\Core\Commands\GenerateTests;
use Levaral\Core\Commands\MakeActionCommand;
use Levaral\Core\Commands\ModelMakeCommand;
use Levaral\Core\Commands\ModelsCommand;
use Illuminate\Support\ServiceProvider;
use Levaral\Core\Commands\GenerateApiJsCommand;
use Levaral\Core\Commands\GenerateDuskTests;
use Levaral\Core\Commands\RefreshMailTemplates;

class CoreServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'core');

        $this->mergeConfigFrom(__DIR__.'/../config/frontlanguages.php', 'frontlanguages');
        $this->mergeConfigFrom(__DIR__.'/../config/mail-templates.php', 'mail-templates');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/core'),
            __DIR__ . '/../resources/views/notifications' => resource_path('views/vendor/notifications'),
            __DIR__ . '/../resources/asset/js/tinymce' => public_path('js/tinymce'),
            __DIR__.'/../config/frontlanguages.php' => config_path('frontlanguages.php'),
            __DIR__.'/../config/mail-templates.php' => config_path('mail-templates.php'),
        ]);
    }

    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateApiJsCommand::class,
                ModelMakeCommand::class,
                ModelsCommand::class,
                GenerateStructure::class,
                MakeActionCommand::class,
                GenerateTests::class,
                GenerateLanguageJson::class,
                CreateUserExpoTokensTable::class,
                CreateMailLogsTable::class,
                CreateMailTemplateTables::class,
                RefreshMailTemplates::class
            ]);
        }
    }

    public function provides()
    {
        return ['core'];
    }
}