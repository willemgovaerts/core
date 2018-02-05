<?php

namespace Levaral\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\RouteCompiler;
use File;

class MakeActionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make Action class';

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
        $name = $this->argument('name');
        $parts = explode('\\', ltrim($name, '\\'));
        $namespace = implode('\\', array_slice($parts, 0, -1));
        $class = array_last($parts);
        $path = str_replace('\\', '/', $namespace);

        $phpTag = '<?php';
        $filePutPath = app_path('Http/Actions/' . $class . '.php');

        $action = 'GetAction';
        if (preg_match('/Post/', $class)) {
            $action = 'PostAction';
        }

        //Create Action Folder
        if (!file_exists(app_path('Http/Actions'))) {
            File::makeDirectory(app_path('Http/Actions'));
        }

        if ($namespace) {
            if (!file_exists(app_path('Http/Actions/' . $path))) {
                File::makeDirectory(app_path('Http/Actions/' . $path), null, true);
            }

            $filePutPath = app_path('Http/Actions/' . $path . '/' . $class . '.php');
        }

        File::put($filePutPath,
            view('core::actionClass', compact('class', 'namespace', 'phpTag', 'action'))->render());
    }
}