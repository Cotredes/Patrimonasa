<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetPhoto extends Model
{
    protected $fillable = ['asset_id', 'path', 'description', 'position', 'is_cover'];

    protected function casts(): array
    {
        return ['is_cover' => 'boolean'];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
