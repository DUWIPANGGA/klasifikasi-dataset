<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dataset extends Model
{
    protected $fillable = [
        'name',
        'description',
        'storage_driver',
        'root_path',
        'google_drive_link',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function duplicateGroups(): HasMany
    {
        return $this->hasMany(DuplicateGroup::class);
    }
}
