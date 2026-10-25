<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = ['uuid', 'name', 'username', 'email', 'password', 'status', 'is_email_verified'];

    protected $hidden = ['password', 'verification_code'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'status' => 'boolean', 'is_email_verified' => 'boolean'];
    }

    public function canAccess(string $permission): bool
    {
        $roles = DB::table('rbac_userroles')->where('UserID', $this->id)->pluck('RoleID');
        if ($roles->contains(1)) {
            return true;
        }

        return DB::table('rbac_rolepermissions as rp')->join('rbac_permissions as p', 'p.ID', '=', 'rp.PermissionID')->whereIn('rp.RoleID', $roles)->where('p.Title', $permission)->exists();
    }
}
