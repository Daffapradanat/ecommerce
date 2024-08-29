<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\EmailChangeVerificationNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'role_id',
        'verification_code',
        'email_change_verification_code',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function can($permission, $arguments = [])
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->role) {
            $permissions = is_array($this->role->permissions) ? $this->role->permissions : json_decode($this->role->permissions, true);

            if (in_array($permission, $permissions)) {
                return true;
            }

            $parts = explode('.', $permission);
            while (count($parts) > 0) {
                array_pop($parts);
                $wildcardPermission = implode('.', $parts) . '.*';
                if (in_array($wildcardPermission, $permissions)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isSuperAdmin()
    {
        return $this->role && $this->role->name === 'superadmin';
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendEmailChangeVerificationNotification()
    {
        $this->notify(new EmailChangeVerificationNotification);
    }
}
