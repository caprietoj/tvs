<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cargo',
        'avatar',
        'active',
        'first_login'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'first_login' => 'boolean',
        ];
    }

    /**
     * Check if user can be impersonated
     */
    public function canBeImpersonated(): bool
    {
        // Prevent impersonating the same user or superadmin
        return $this->id !== auth()->id() && !$this->hasRole('SuperAdmin');
    }

    /**
     * Get the default user image for AdminLTE
     */
    public function adminlte_image()
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return Storage::url($this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the user description for AdminLTE
     */
    public function adminlte_desc()
    {
        return $this->cargo ?? 'Usuario';
    }

    /**
     * Get the user profile URL for AdminLTE
     */
    public function adminlte_profile_url()
    {
        return 'admin/settings';
    }
}
