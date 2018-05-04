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
        $phpTag = '<?php';
        //Create Domain Folder
        if (!file_exists(app_path('Domain'))) {
            File::makeDirectory(app_path('Domain'));
        }

        //Create Action Folder
        if (!file_exists(app_path('Http/Actions'))) {
            File::makeDirectory(app_path('Http/Actions'));
        }

        //Generate User Domain
        if (!file_exists(app_path('Domain/User'))) {
            File::makeDirectory(app_path('Domain/User'), null, true);
            File::put(app_path('Domain/User/User.php'), view('core::User', compact('phpTag'))->render());
            File::put(app_path('Domain/User/UserQuery.php'), view('core::UserQuery', compact('phpTag'))->render());
        }

        //Generate MailLog Domain
        if (!file_exists(app_path('Domain/MailLog'))) {
            File::makeDirectory(app_path('Domain/MailLog'), null, true);
            File::put(app_path('Domain/MailLog/MailLog.php'), view('core::MailLog', compact('phpTag'))->render());
            File::put(app_path('Domain/MailLog/MailLogQuery.php'), view('core::MailLogQuery', compact('phpTag'))->render());
        }

        //Generate MailLog Domain
        if (!file_exists(app_path('Domain/MailTemplate'))) {
            File::makeDirectory(app_path('Domain/MailTemplate'), null, true);
            File::put(app_path('Domain/MailTemplate/MailTemplate.php'), view('core::MailTemplate', compact('phpTag'))->render());
        }

        if (!file_exists(app_path('Http/Actions/GetAction.php'))) {
            File::put(app_path('Http/Actions/GetAction.php'), view('core::GetAction', compact('phpTag'))->render());
        }

        if (!file_exists(app_path('Http/Actions/PostAction.php'))) {
            File::put(app_path('Http/Actions/PostAction.php'), view('core::PostAction', compact('phpTag'))->render());
        }

        exec('sed -i "s/protected \$namespace = .*/protected \$namespace = null;/" app/Providers/RouteServiceProvider.php');
    }
}