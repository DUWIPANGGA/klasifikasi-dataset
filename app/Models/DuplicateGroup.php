<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DuplicateGroup extends Model
{
    protected $fillable = [
        'dataset_id',
        'hash',
        'type',
    ];

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'duplicate_group_images');
    }

    public function isCrossLabel(): bool
    {
        return $this->type === 'cross_label';
    }
}
