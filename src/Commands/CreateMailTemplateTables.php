<?php

namespace Levaral\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;

class CreateMailTemplateTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'levaral:mailtemplates:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates mail template tables.';

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
//        $this->createLocalesTable();
        $this->createMailTemplatesTable();
        $this->createMailTemplateContentsTable();

        //Creates models
        Artisan::call('levaral:models', ['model' => 'App\Domain\MailTemplate\MailTemplate']);
        Artisan::call('levaral:models', ['model' => 'App\Domain\MailTemplate\MailTemplateContent']);

        $this->info('Table generated successfully.');
    }

    protected function createLocalesTable()
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->increments('id');
            $table->string('language_code')->nullable();
            $table->string('name')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    protected function createMailTemplatesTable()
    {
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->text('variables')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    protected function createMailTemplateContentsTable()
    {
        Schema::create('mail_template_contents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('locale')->nullable();
            $table->unsignedInteger('mail_template_id');
            $table->text('subject')->nullable();
            $table->text('content')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
}