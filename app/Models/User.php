<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'id_number',
        'is_active', 'otp_code', 'otp_expires_at', 'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token', 'otp_code'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at'    => 'datetime',
            'otp_sent_at'       => 'datetime',
            'otp_attempts'      => 'integer',
            'password'          => 'hashed',   // automatic password hashing
            'is_active'         => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function borrowingRequests()
    {
        return $this->hasMany(BorrowingRequest::class);
    }

    public function scopeBorrowers($query)
    {
        return $query->where('role', 'borrower');
    }
}
