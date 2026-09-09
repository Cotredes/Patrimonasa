<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = ['asset_id', 'title', 'original_name', 'path', 'mime_type', 'size', 'type', 'document_date', 'expires_at', 'notes', 'is_important', 'uploaded_by'];

    protected function casts(): array
    {
        return ['document_date' => 'date', 'expires_at' => 'date', 'is_important' => 'boolean'];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
