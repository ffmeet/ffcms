<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Slimani\MediaManager\Models\File as MediaFile;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'media_file_id',
        'filename',
        'filepath',
        'mime_type',
        'size',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'media_file_id');
    }

    public function getUrlAttribute(): ?string
    {
        if (blank($this->filepath)) {
            return null;
        }

        return Storage::disk('public')->url($this->filepath);
    }

    public function getReadableSizeAttribute(): string
    {
        $bytes = (int) $this->size;

        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / 1024 / 1024, 2).' MB';
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }
}
