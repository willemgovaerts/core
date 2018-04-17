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
        $languages = [];
        $languageDirectories = array_diff(scandir(resource_path('lang')), array('..', '.'));

        foreach ($languageDirectories as $languageDirectory) {
            if (!in_array($languageDirectory, config('frontlanguages.languages.locales'))) {
                continue;
            }

            $directory = resource_path('lang/' . $languageDirectory);
            $files = File::allFiles($directory);

            foreach ($files as $file) {
                $fileName = str_replace(".".pathinfo($file->getFileName(), PATHINFO_EXTENSION),
                    '', $file->getFileName());

                foreach (config('frontlanguages.languages.groups') as $groupName => $group) {
                    foreach ($group as $languageFile) {
                        if ($languageFile !==  $fileName) {
                            continue;
                        }

                        $languages[$fileName] = require($file);
                        $this->generateFiles(json_encode($languages), $groupName.'-'.$languageDirectory);
                    }
                }
            }
        }
    }

    protected function generateFiles($jsonData, $languageFile)
    {
        return file_put_contents(resource_path('assets/js/' . $languageFile . '.js'),
            view('core::languageJson', compact('jsonData', 'languageFile'))->render());
    }
}