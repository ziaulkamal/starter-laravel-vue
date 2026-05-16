<?php

namespace App\Http\Controllers\Mtq\Master;

use App\Http\Controllers\Controller;
use Database\Seeders\Mtq\MtqMasterSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ResetController extends Controller
{
    public function reset(): RedirectResponse
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('golongans')->truncate();
        DB::table('kriterias')->truncate();
        DB::table('cabangs')->truncate();
        DB::table('kafilahs')->truncate();
        DB::table('venues')->truncate();
        DB::table('konfigurasi_events')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Artisan::call('db:seed', [
            '--class' => MtqMasterSeeder::class,
            '--force' => true,
        ]);

        return back()->with('success', 'Semua data master M1 telah direset ke data default.');
    }
}
