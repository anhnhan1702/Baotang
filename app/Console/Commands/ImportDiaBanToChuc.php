<?php

namespace App\Console\Commands;

use App\Models\DiaBan;
use App\Models\ToChuc;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportDiaBanToChuc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-dia-ban-to-chuc {filename} {email}';

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
        $inputFileName = base_path('database/seed_data/'.$this->argument('filename'));

        /** Create a new Xls Reader  **/
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        //    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        //    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xml();
        //    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Ods();
        //    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Slk();
        //    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Gnumeric();
        //    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        /** Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = $reader->load($inputFileName);

        $startRow = 2;
        $firstColumnValue = $spreadsheet->getActiveSheet()->getCell('A'.$startRow)->getValue();

        if($firstColumnValue) {
            $tinh = DiaBan::firstOrcreate([
                'name' => $firstColumnValue,
                'code' => $spreadsheet->getActiveSheet()->getCell('B'.$startRow)->getValue()
            ]);
            $tinhToChuc = ToChuc::firstOrCreate([
                'dia_ban_id' => 1,
                'name' => 'UBND '.$firstColumnValue,
            ]);
            // Create User
            $tinhEmailAlias = Str::of($tinhToChuc->name)->slug('');
            $email = $tinhEmailAlias.$emailDomain;
            $findUser = User::where('email', $email)->first();
            if(!$findUser) {
                User::firstOrcreate([
                    'to_chuc_id' => $tinhToChuc->id,
                    'name' => $tinhToChuc->name,
                    'email' => Str::of($tinhToChuc->name)->slug('').$emailDomain,
                    'email_verified_at' => now(),
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                ]);
            } else {
                $findUser->to_chuc_id = $tinhToChuc->id;
                $findUser->save();
            }
        }

        while($firstColumnValue) {
            $name = $spreadsheet->getActiveSheet()->getCell('C'.$startRow)->getValue();
            $code = $spreadsheet->getActiveSheet()->getCell('D'.$startRow)->getValue();
            
            // QH
            $this->info($name);
            $quanHuyen = DiaBan::firstOrCreate([
                'parent_id' => 1,
                'name' => $name,
                'code' => $code,
            ]);
            $quanHuyen = DiaBan::firstOrCreate([
                'parent_id' => 1,
                'name' => $name,
                'code' => $code,
            ]);
            $quanHuyenToChuc = ToChuc::firstOrCreate([
                'dia_ban_id' => $quanHuyen->id,
                'name' => 'UBND '.$quanHuyen->name,
            ]);
            // Create User
            $huyenEmailAlias = Str::of($quanHuyen->name)->slug('');
            $findUser = User::where('email', $email)->first();
            if(!$findUser) {
                User::firstOrcreate([
                    'to_chuc_id' => $quanHuyenToChuc->id,
                    'name' => $quanHuyenToChuc->name,
                    'email' => Str::of($quanHuyenToChuc->name)->slug('').$emailDomain,
                    'email_verified_at' => now(),
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                ]);
            } else {
                $findUser->to_chuc_id = $quanHuyenToChuc->id;
                $findUser->save();
            }

            // PX
            $phuongXaName = $spreadsheet->getActiveSheet()->getCell('E'.$startRow)->getValue();
            $phuongXaCode = $spreadsheet->getActiveSheet()->getCell('F'.$startRow)->getValue();
            $phuongXaLevel = $spreadsheet->getActiveSheet()->getCell('G'.$startRow)->getValue();
            $this->info("-- ".$phuongXaName);
            $phuongXa = DiaBan::firstOrCreate([
                'parent_id' => $quanHuyen->id,
                'name' => $phuongXaName,
                'code' => $phuongXaCode,
                'level' => $phuongXaLevel,
            ]);
            $phuongXaToChuc = ToChuc::firstOrCreate([
                'dia_ban_id' => $phuongXa->id,
                'name' => 'UBND '.$phuongXa->name,
            ]);

            // Create User
            $email = Str::of($phuongXaToChuc->name)->slug('').'.'.$huyenEmailAlias.$emailDomain;
            $findUser = User::where('email', $email)->first();
            if(!$findUser) {
                User::firstOrcreate([
                    'to_chuc_id' => $phuongXaToChuc->id,
                    'name' => $phuongXaToChuc->name,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                ]);
            } else {
                $findUser->to_chuc_id = $phuongXaToChuc->id;
                $findUser->save();
            }

            $startRow++;
            $firstColumnValue = $spreadsheet->getActiveSheet()->getCell('A'.$startRow)->getValue();
        }
    }
}
