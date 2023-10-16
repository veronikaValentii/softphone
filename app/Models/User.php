<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ACTIVE = 'active';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'phone_number'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    public function store(array $attributes): Model
    {
        $user = User::query()->create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password' => Crypt::encrypt($attributes['password']),
            'status' => self::STATUS_INACTIVE
        ]);
        //send activation account code
        (new UserConfirmation())->send($attributes);

        return $user;
    }

    public function activate(array $attributes): Model
    {
        //disable old activation codes
        UserConfirmation::query()
            ->where('type',UserConfirmation::TYPE_EMAIL)
            ->where('context', $attributes['email'])
            ->where('is_active',true)
            ->update(['is_active' => false]);

        return User::query()->updateOrCreate([
            'email' => $attributes['email'],
        ], [
            'status' => self::STATUS_ACTIVE
        ]);
    }

}
