<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Log extends Model
{
    use HasFactory;

    protected $fillable = ['action_id', 'document_id', 'bundle_id', 'user_id', 'office_id', 'assigned_to','description', 'endorsed_to', 'remarks'];

    public function documents(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(User::class, 'office_id', 'office_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
