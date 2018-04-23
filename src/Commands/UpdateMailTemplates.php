<?php

namespace Levaral\Core\Commands;

use Illuminate\Console\Command;
use Levaral\Core\Services\MailTemplateService;

class UpdateMailTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'levaral:update:mailtemplates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update mail templates.';

    protected $mailTemplateService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MailTemplateService $mailTemplateService)
    {
        parent::__construct();
        $this->mailTemplateService = $mailTemplateService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->mailTemplateService->createMailTemplates();
    }
}