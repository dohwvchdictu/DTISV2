<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * What a document is, in one line, for every list and heading that used to
     * print the category outright.
     *
     * A Citizen's Charter transaction has no category - the charter procedure it
     * falls under is its classification - so the procedure stands in. Category
     * still wins where both exist: documents encoded before the charter form
     * dropped the category select have both, and they should keep reading the
     * way they always have.
     *
     * Reads through the relations, so eager-load `category` and `citizencharter`
     * on anything that renders a list of these.
     */
    protected function classification(): Attribute
    {
        return Attribute::get(
            fn () => $this->category?->name ?? $this->citizencharter?->name ?? '—'
        );
    }
}
