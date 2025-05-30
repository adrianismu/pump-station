<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; 

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relasi many-to-many dengan PumpHouse
     */
    public function pumpHouses()
    {
        return $this->belongsToMany(PumpHouse::class, 'user_pump_house')
                    ->withPivot(['access_level', 'is_active', 'assigned_at', 'expires_at', 'notes'])
                    ->withTimestamps()
                    ->wherePivot('is_active', true)
                    ->where(function($query) {
                        $query->whereNull('user_pump_house.expires_at')
                              ->orWhere('user_pump_house.expires_at', '>', now());
                    });
    }

    /**
     * Relasi untuk semua pump houses (termasuk yang tidak aktif)
     */
    public function allPumpHouses()
    {
        return $this->belongsToMany(PumpHouse::class, 'user_pump_house')
                    ->withPivot(['access_level', 'is_active', 'assigned_at', 'expires_at', 'notes'])
                    ->withTimestamps();
    }

    public function isAdmin()
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    public function isPetugas()
    {
        return $this->role === 'petugas' || $this->hasRole('petugas');
    }

    /**
     * Cek apakah user memiliki akses ke pump house tertentu
     */
    public function hasAccessToPumpHouse($pumpHouseId, $accessLevel = 'read')
    {
        // Admin memiliki akses ke semua pump house
        if ($this->isAdmin()) {
            return true;
        }

        // Cek akses spesifik untuk petugas
        $access = $this->pumpHouses()
                      ->where('pump_houses.id', $pumpHouseId)
                      ->first();

        if (!$access) {
            return false;
        }

        // Mapping level akses
        $levels = ['read' => 1, 'write' => 2, 'admin' => 3];
        $userLevel = $levels[$access->pivot->access_level] ?? 0;
        $requiredLevel = $levels[$accessLevel] ?? 1;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Get pump house IDs yang dapat diakses user
     */
    public function getAccessiblePumpHouseIds()
    {
        if ($this->isAdmin()) {
            return PumpHouse::pluck('id')->toArray();
        }

        return $this->pumpHouses()->pluck('pump_houses.id')->toArray();
    }

    /**
     * Get pump house IDs dengan level akses tertentu
     */
    public function getAccessiblePumpHouseIdsByLevel($accessLevel = 'read')
    {
        if ($this->isAdmin()) {
            return PumpHouse::pluck('id')->toArray();
        }

        // Mapping level akses
        $levels = ['read' => 1, 'write' => 2, 'admin' => 3];
        $requiredLevel = $levels[$accessLevel] ?? 1;

        return $this->pumpHouses()
            ->wherePivot('is_active', true)
            ->where(function($query) {
                $query->whereNull('user_pump_house.expires_at')
                      ->orWhere('user_pump_house.expires_at', '>', now());
            })
            ->get()
            ->filter(function($pumpHouse) use ($levels, $requiredLevel) {
                $userLevel = $levels[$pumpHouse->pivot->access_level] ?? 0;
                return $userLevel >= $requiredLevel;
            })
            ->pluck('id')
            ->toArray();
    }

    /**
     * Cek apakah user dapat melakukan write operation pada pump house
     */
    public function canWriteToPumpHouse($pumpHouseId)
    {
        return $this->hasAccessToPumpHouse($pumpHouseId, 'write');
    }

    /**
     * Cek apakah user dapat melakukan admin operation pada pump house
     */
    public function canAdminPumpHouse($pumpHouseId)
    {
        return $this->hasAccessToPumpHouse($pumpHouseId, 'admin');
    }
}