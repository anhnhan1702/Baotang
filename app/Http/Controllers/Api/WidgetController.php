<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiaBan;
use App\Models\ToChuc;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WidgetController extends Controller
{
    public function countBtsTheoHuyen()
    {
        $huyens = DiaBan::whereNull('level')->get();
        
        $labels = [];
        foreach($huyens as $huyen) {
            $labels[] = $huyen->name;
            $count = 0;
            $toChucs = ToChuc::where('dia_ban_id', $huyen->id)->get();
            foreach($toChucs as $toChuc) {
                $users = User::where('to_chuc_id', $toChuc->id)->select(['id', 'to_chuc_id'])->get();
                foreach($users as $user) {
                    $count = $count + DB::table('db_du_lieu_bts')->where('user_id', $user->id)->count();
                }
            }
            $data[] = $count;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }
}
