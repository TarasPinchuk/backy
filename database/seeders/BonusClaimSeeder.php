<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bonus;
use App\Models\VolunteerRecipient;
use App\Models\BonusClaim;

class BonusClaimSeeder extends Seeder
{
    public function run()
    {
        // ——————————————————————————
        // Компания 1: первые 2 бонуса получают первые 2 волонтёра
        // ——————————————————————————
        $company1Id = 1;
        $bonuses1   = Bonus::where('company_id', $company1Id)->take(2)->get();
        $vols1      = VolunteerRecipient::where('company_id', $company1Id)->take(2)->get();

        foreach ($bonuses1 as $i => $bonus) {
            if (! isset($vols1[$i])) {
                continue;
            }
            BonusClaim::create([
                'bonus_id'               => $bonus->id,
                'volunteer_recipient_id' => $vols1[$i]->id,
                'claimed_at'             => now()->subDays($i + 1),
            ]);
            // Отметим бонус как использованный
            $bonus->update(['is_used' => true]);
        }

        // ——————————————————————————
        // Компания 2: 2 следующих бонуса получают следующих 2 волонтёра
        // ——————————————————————————
        $company2Id = 2;
        $bonuses2   = Bonus::where('company_id', $company2Id)->skip(2)->take(2)->get();
        // пропустим первых 2 волонтёра и возьмём следующих 2
        $vols2 = VolunteerRecipient::where('company_id', $company2Id)
                    ->skip(2)
                    ->take(2)
                    ->get();

        foreach ($bonuses2 as $i => $bonus) {
            if (! isset($vols2[$i])) {
                continue;
            }
            BonusClaim::create([
                'bonus_id'               => $bonus->id,
                'volunteer_recipient_id' => $vols2[$i]->id,
                'claimed_at'             => now()->subDays($i + 3),
            ]);
            $bonus->update(['is_used' => true]);
        }
    }
}