<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'slug', 'plan', 'settings'];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $tenant) {
            if (!$tenant->slug) {
                $tenant->slug = Str::slug($tenant->name) . '-' . Str::random(6);
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
