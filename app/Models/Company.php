<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Company extends Authenticatable
{
use HasApiTokens, Notifiable;

protected $fillable = [
'name',
'email',
'password',
];

protected $hidden = [
'password',
];
}
