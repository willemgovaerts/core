<?php

namespace Levaral\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\RouteCompiler;

class GenerateBrowserTest extends command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'levaral:generate-browser-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To generate dusk test classes for actions';

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
        $p = [];
        foreach (app('router')->getRoutes() as $route) {

            $compiledRoute = (new RouteCompiler($route))->compile();

            if (substr($route->uri, 0, 3) === 'api'
                || !isset($route->action['controller']) || !$compiledRoute) {
                continue;
            }
            $action = $route->action;

            $parts = explode('Actions\\', $action['controller']);
            $p[] = $parts;
            if (count($parts) < 2) {
                continue;
            }

            $actionArray = explode('\\', $parts[1]);

            if (count($actionArray) < 2) {
                continue;
            }

            $namespaces = $this->generateFiles($route, $actionArray, $namespaces, "");
        }

        return $namespaces;
    }

    private function generateFiles($route, array $actionArray, array $namespaces, string $classpath)
    {
        $phpTag = '<?php';

        $namespace = $actionArray[0];
        $method = $actionArray[1];

        if (count($actionArray) > 2) {
            array_shift($actionArray);
            if (!array_key_exists($namespace, $namespaces)) {
                $namespaces[$namespace] = [];
            }
            $classpath = $classpath . "/" . $namespace;
            $namespaces[$namespace] = $this->generateFiles($route, $actionArray, $namespaces[$namespace], $classpath);
        } else {
            $temp = [
                'method' => strtolower($route->methods[0]),
                'name' => $method,
                'path' => str_replace('?', '', str_replace('{', '${', $route->uri)),
                'classpath' => $classpath . "/" . $namespace,
                'routeName' => $route->action['as'],
            ];
            $namespaces[$namespace][] = $temp;

            $file = 'tests/Browser'.$temp['classpath']."/".$temp['name']."Test.php";
            $url = $route->uri;

            if (!file_exists($file)) {
                if (!file_exists(dirname($file))) {
                    mkdir(dirname($file), 0777, true);
                }
                file_put_contents($file, view('core::DuskPageTest', compact('temp', 'phpTag', 'cl', 'url'))->render());
            }
        }

        return $namespaces;
    }
}