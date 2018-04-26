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
        $languageDirectories = array_diff(scandir(resource_path('lang')), array('..', '.'));

        foreach ($languageDirectories as $languageDirectory) {
            if (!in_array($languageDirectory, config('frontlanguages.languages.locales'))) {
                continue;
            }

            foreach (config('frontlanguages.languages.groups') as $groupName => $group) {
                $languages = [];
                foreach ($group as $languageFile) {
                    $langFilePath = resource_path('lang/' . $languageDirectory.'/'.$languageFile.'.php');

                    if (!file_exists($langFilePath)) {
                        continue;
                    }

                    $fileName = basename($langFilePath);
                    $fileNameWithoutExtension = str_replace(".".pathinfo($fileName, PATHINFO_EXTENSION), '', $fileName);
                    $languages[$fileNameWithoutExtension] = require($langFilePath);
                }
                $this->generateFiles(json_encode($languages), $groupName.'-'.$languageDirectory);
            }
        }
    }

    protected function generateFiles($jsonData, $languageFile)
    {
        return file_put_contents(resource_path('assets/js/' . $languageFile . '.js'),
            view('core::languageJson', compact('jsonData', 'languageFile'))->render());
    }
}