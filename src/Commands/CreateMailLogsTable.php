<?php
namespace Levaral\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;

class CreateMailLogsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'levaral:maillog:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates maillog table for log notifications.';

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
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableMorphs('model');
            $table->string('mail_type')->nullable();
            $table->string('response_code')->nullable();
            $table->text('error_reason')->nullable();
            $table->timestamp('mail_sent_at')->nullable();
            $table->timestamp('mail_fail_at')->nullable();
            $table->timestamp('mail_clicked_at')->nullable();
            $table->timestamp('mail_opened_at')->nullable();
            $table->timestamp('mail_unsubscribed_at')->nullable();
            $table->timestamp('mail_complained_at')->nullable();
            $table->timestamp('mail_stored_at')->nullable();
            $table->timestamps();
        });

        //Creates models for the models "MailLog"
        Artisan::call('levaral:models', ['model'=>'App\Domain\MailLog\MailLog']);

        echo "Table generated successfully.\n";
    }
}