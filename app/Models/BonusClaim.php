<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonusClaim extends Model
{
    protected $fillable = [
        'bonus_id',
        'volunteer_recipient_id',
        'claimed_at',
    ];

    public function bonus()
    {
        return $this->belongsTo(Bonus::class);
    }

    public function volunteerRecipient()
    {
        return $this->belongsTo(VolunteerRecipient::class, 'volunteer_recipient_id');
    }
}
