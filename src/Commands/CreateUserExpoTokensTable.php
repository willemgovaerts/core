<?php
namespace Levaral\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;

class CreateUserExpoTokensTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'levaral:user-expo-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates user expo tokens table for expo push notifications.';

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
        Schema::create('user_expo_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('expo_token');
            $table->timestamps();
        });

        //Create model for the schema-table
        Artisan::call('make:model', ['name' => 'App\Domain\User\UserExpoToken']);

        //Creates models for the models "UserExpoTokens"
        Artisan::call('levaral:models', ['model'=>'App\Domain\User\UserExpoToken']);

        echo "Table generated successfully.\n";
    }
}