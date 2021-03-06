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
        $this->createMailTemplatesTables();

        Artisan::call('make:model', ['name' => 'App\Domain\MailTemplate\MailTemplate']);
        Artisan::call('make:model', ['name' => 'App\Domain\MailTemplate\MailTemplateContent']);

        //Creates models
        //Artisan::call('levaral:models', ['model' => 'App\Domain\MailTemplate\MailTemplate']);
        //Artisan::call('levaral:models', ['model' => 'App\Domain\MailTemplate\MailTemplateContent']);

        $this->info('Table generated successfully.');
    }


    protected function createMailTemplatesTables()
    {
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mail_template_contents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('locale')->nullable();
            $table->unsignedInteger('mail_template_id');
            $table->text('subject')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('mail_template_id')->references('id')->on('mail_templates')->onDelete('cascade');
        });
    }
}