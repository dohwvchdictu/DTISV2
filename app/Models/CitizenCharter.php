<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CitizenCharter extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'office_id', 'is_external', 'is_active', 'required_days'];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function bundles(): HasMany
    {
        return $this->hasMany(Bundle::class);
    }
}
