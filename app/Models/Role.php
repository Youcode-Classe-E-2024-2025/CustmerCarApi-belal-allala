<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
    ];

    /**
     * Les utilisateurs qui appartiennent à ce rôle.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}