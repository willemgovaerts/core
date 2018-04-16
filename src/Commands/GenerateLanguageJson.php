<?php
namespace Levaral\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateLanguageJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'levaral:generate-language-json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates JSON files for languages';

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
        $languageArray = [];
        $languageDirectories = array_diff(scandir(resource_path('lang')), array('..', '.'));

        foreach($languageDirectories as $languageDirectory) {

            if(in_array($languageDirectory, config('frontlanguages.languages.locales'))) {
                $directory = resource_path('lang/' . $languageDirectory);

                $files = File::allFiles($directory);

                foreach ($files as $file) {
                    $fileName = array_last(explode("/", $file));
                    $nameKey = str_replace(".php", "", $fileName);
                    if(in_array($nameKey, config('frontlanguages.languages.groups.front')) || in_array($nameKey, config('frontlanguages.languages.groups.admin'))) {
                        $languageArray[$languageDirectory][$nameKey] = require($file);
                    }
                }

                $this->generateFiles(json_encode($languageArray), $languageDirectory);
            }
        }
        return true;
    }

    protected function generateFiles($jsonData, $languageFile)
    {
        return file_put_contents(resource_path('assets/js/' . $languageFile . '.js'), view('core::languageJson', compact('jsonData', 'languageFile'))->render());
    }
}