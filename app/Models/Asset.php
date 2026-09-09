<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['category_id', 'name', 'slug', 'description', 'notes', 'details', 'is_favorite', 'archived_at'];

    protected function casts(): array
    {
        return ['details' => 'array', 'is_favorite' => 'boolean', 'archived_at' => 'datetime'];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function photos()
    {
        return $this->hasMany(AssetPhoto::class)->orderBy('position');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class)->orderBy('due_date');
    }

    public function getCoverPhotoAttribute()
    {
        return $this->photos->firstWhere('is_cover', true) ?: $this->photos->first();
    }
}
