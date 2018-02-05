<?php

namespace Levaral\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\RouteCompiler;
use File;

class GenerateStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'levaral:structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To generate base structure';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Create Domain Folder
        if (!file_exists(app_path('Domain'))) {
            File::makeDirectory(app_path('Domain'));
        }

        //Create Action Folder
        if (!file_exists(app_path('Http/Actions'))) {
            File::makeDirectory(app_path('Http/Actions'));
        }

        //Create DTO Folder
        if (!file_exists(app_path('DTO'))) {
            File::makeDirectory(app_path('DTO'));
        }

        //Create DTO Folder
        if (!file_exists(app_path('Criteria'))) {
            File::makeDirectory(app_path('Criteria'));
        }

        //Generate User Domain
        if (!file_exists(app_path('Domain/User'))) {
            File::copyDirectory(__DIR__ . '/../../resources/stubs/User', app_path('Domain/User'));
        }

        if (!file_exists(app_path('Http/Actions/GetAction.php'))) {
            File::copy(__DIR__ . '/../../resources/stubs/Actions/GetAction.php', app_path('Http/Actions/GetAction.php'));
        }

        if (!file_exists(app_path('Http/Actions/PostAction.php'))) {
            File::copy(__DIR__ . '/../../resources/stubs/Actions/PostAction.php', app_path('Http/Actions/PostAction.php'));
        }

        exec('sed -i "s/protected \$namespace = .*/protected \$namespace = null;/" app/Providers/RouteServiceProvider.php');
    }
}