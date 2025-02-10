<?php

namespace App\Console\Commands;

use App\Models\ToChuc;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateUserToChuc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-user-to-chuc {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $emailDomain = '@'.$this->argument('email');

        $toChucs = ToChuc::all();
        foreach($toChucs as $toChuc) {
            $tinhEmailAlias = Str::of($toChuc->name)->slug('');
            $email = $tinhEmailAlias.$emailDomain;
            $findUser = User::where('email', $email)->first();
            if($findUser) {
                $findUser->to_chuc_id = $toChuc->id;
                $findUser->save();
            }
        }
    }
}
