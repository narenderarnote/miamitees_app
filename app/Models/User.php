<?php

namespace App\Models;

use Laravel\Spark\User as SparkUser;
use Illuminate\Database\Eloquent\Model;

class User extends SparkUser
{
    const STATUS_NEW    = 'new';
    const STATUS_ACTIVE = 'active';
    const STATUS_BANNED = 'banned';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'authy_id',
        'country_code',
        'phone',
        'two_factor_reset_code',
        'card_brand',
        'card_last_four',
        'card_country',
        'billing_address',
        'billing_address_line_2',
        'billing_city',
        'billing_zip',
        'billing_country',
        'extra_billing_information',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'uses_two_factor_auth' => 'boolean',
    ];

    public function setPassword($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function createUser()
    {
        $this->status = static::STATUS_NEW;
        $result = $this->save();

        \Event::fire(new \App\Events\User\UserCreatedEvent($this));

        return $result;
    }

    public function changeStatusTo($status)
    {
        $this->status = $status;
        $result = $this->save();

        // $result check, because we need to trigger only if really changed
        if ($result) {
            \Event::fire(new \App\Events\User\UserStatusChangedEvent($this));
        }

        return $result;
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }
    
}
