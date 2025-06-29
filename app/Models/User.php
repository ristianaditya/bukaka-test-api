<?php

namespace App\Models;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    protected $table = "users";
    protected $primaryKey = "id";
    protected $KeyType = "int";
    public $timeStamps = true;
    public $incrementing = true;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'email',
        'name',
        'password',
        'no_phone',
        'token',
        'role',
        'sim',
        'address'
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
}
