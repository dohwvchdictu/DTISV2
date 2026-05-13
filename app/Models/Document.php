<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'bundle_id', 'citizen_charter_id', 'office_id', 'user_id', 'assigned_to', 'endorsed_to','control_no', 'source', 'is_arta', 'is_bundle','subject', 'turnaroundtime', 'status'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function citizencharter(): BelongsTo
    {
        return $this->belongsTo(CitizenCharter::class, 'citizen_charter_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }
}
