<?php

namespace App\Models;

use App\Enums\ImageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'dataset_id',
        'filename',
        'filepath',
        'image_url',
        'thumbnail',
        'source',
        'title',
        'width',
        'height',
        'hash',
        'downloaded_at',
        'fish_name',
        'common_name',
        'label',
        'query',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'downloaded_at' => 'datetime',
            'width' => 'integer',
            'height' => 'integer',
            'status' => ImageStatus::class,
            'file_exists' => 'boolean',
            'file_checked_at' => 'datetime',
        ];
    }

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function duplicateGroups(): BelongsToMany
    {
        return $this->belongsToMany(DuplicateGroup::class, 'duplicate_group_images');
    }

    public function isDuplicate(): bool
    {
        return $this->duplicateGroups()->count() > 0;
    }

    public function isCrossLabelDuplicate(): bool
    {
        return $this->duplicateGroups()->where('type', 'cross_label')->count() > 0;
    }

    public function isHealthy(): bool
    {
        return $this->label === 'healthy';
    }
}
