<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = ['asset_id', 'document_id', 'title', 'due_date', 'completed'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'completed' => 'boolean'];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
