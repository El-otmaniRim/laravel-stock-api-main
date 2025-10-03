<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @method bool hasRole(string $role)
 * @method bool hasAnyRole(...$roles)
 * @method $this assignRole(string|array|\Spatie\Permission\Models\Role ...$roles)
 * @method $this removeRole(string|array|\Spatie\Permission\Models\Role ...$roles)
 * @method bool hasPermissionTo(string $permission)
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
