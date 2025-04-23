<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $fillable = [
        'company_id','name','description','level','is_used'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function claim()
    {
        return $this->hasOne(BonusClaim::class);
    }
}
