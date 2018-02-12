<?php

namespace Levaral\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\RouteCompiler;

class GenerateApiJsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'levaral:api-js';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To generate api routes/api services in plain javascript objects';

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
        $namespaces = [];
        foreach (app('router')->getRoutes() as $route) {
            $compiledRoute = (new RouteCompiler($route))->compile();
            if (substr($route->uri, 0, 3) !== 'api'
                || !isset($route->action['controller']) || !$compiledRoute) {
                continue;
            }
            $action = $route->action;
            $parts = explode('Actions\\', $action['controller']);
            if (count($parts) < 2) {
                continue;
            }
            $actionArray = array_reverse(explode('\\', $parts[1]));
            if (count($actionArray) < 2) {
                continue;
            }
            $namespace = $actionArray[1];
            $method = $actionArray[0];
            $params = $compiledRoute->getVariables();
            array_push($params, 'formData');
            $namespaces[$namespace][] = [
                'method' => strtolower($route->methods[0]),
                'name' => $method,
                'params' => $params,
                'path' => str_replace('?', '', str_replace('{', '${', $route->uri))
            ];
        }
        return file_put_contents(resource_path('assets/js/api.js'), view('stubs.apiClass', compact('namespaces'))->render());
    }
}
