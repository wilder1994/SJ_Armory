<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'checksum',
        'uploaded_by',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

