<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuplicateGroupImage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'duplicate_group_id',
        'image_id',
    ];

    public function duplicateGroup(): BelongsTo
    {
        return $this->belongsTo(DuplicateGroup::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }
}
