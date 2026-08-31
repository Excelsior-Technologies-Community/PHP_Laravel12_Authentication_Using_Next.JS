<?php

namespace App\Models;

use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Auth extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'auth';

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'email_verified_at',

        // Security
        'failed_login_attempts',
        'locked_until',

        // Last login
        'last_login_at',
        'last_login_ip',
        'last_login_user_agent',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',

            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',

            'failed_login_attempts' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Email Verification
    |--------------------------------------------------------------------------
    */

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail());
    }

    /*
    |--------------------------------------------------------------------------
    | Password Reset
    |--------------------------------------------------------------------------
    */

    public function sendPasswordResetNotification($token): void
    {
        $url = config('app.frontend_url')
            . '/reset-password?token='
            . urlencode($token)
            . '&email='
            . urlencode($this->email);

        $this->notify(
            new CustomResetPassword($url)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Check Account Lock
    |--------------------------------------------------------------------------
    */

    public function isLocked(): bool
    {
        if (!$this->locked_until) {
            return false;
        }

        if ($this->locked_until->isFuture()) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Lock expired
        |--------------------------------------------------------------------------
        */

        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Check Active Account
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
