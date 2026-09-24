<?php

namespace App\Models;

use App\Enums\ImageStatus;
use App\Support\GoogleDriveHelper;
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

    /**
     * Get direct display URL for the image.
     */
    public function getDisplayUrlAttribute(): ?string
    {
        $url = $this->attributes['image_url'] ?? null ?: ($this->attributes['thumbnail'] ?? null);

        if ($url && GoogleDriveHelper::isGoogleDriveUrl($url)) {
            return GoogleDriveHelper::toDirectImageUrl($url);
        }

        if (!$url && $this->filepath) {
            $storage = app(\App\Services\Storage\DatasetStorageInterface::class);
            return $storage->getFileUrl($this->filepath);
        }

        return $url;
    }

    /**
     * Get direct thumbnail URL for the image.
     */
    public function getDisplayThumbnailAttribute(): ?string
    {
        $thumb = $this->attributes['thumbnail'] ?? null ?: ($this->attributes['image_url'] ?? null);

        if ($thumb && GoogleDriveHelper::isGoogleDriveUrl($thumb)) {
            return GoogleDriveHelper::toThumbnailUrl($thumb, 400);
        }

        if (!$thumb && $this->filepath) {
            $storage = app(\App\Services\Storage\DatasetStorageInterface::class);
            return $storage->getFileUrl($this->filepath);
        }

        return $thumb;
    }

    /**
     * Accessor for image_url: converts Google Drive links to direct image URLs.
     */
    public function getImageUrlAttribute(?string $value): ?string
    {
        if ($value && GoogleDriveHelper::isGoogleDriveUrl($value)) {
            return GoogleDriveHelper::toDirectImageUrl($value);
        }

        return $value;
    }

    /**
     * Accessor for thumbnail: converts Google Drive links to direct thumbnail URLs.
     */
    public function getThumbnailAttribute(?string $value): ?string
    {
        if ($value && GoogleDriveHelper::isGoogleDriveUrl($value)) {
            return GoogleDriveHelper::toThumbnailUrl($value, 400);
        }

        return $value;
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
