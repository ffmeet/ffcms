<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostStatistic extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'post_id';

    protected $fillable = [
        'post_id',
        'views',
        'likes',
        'comments_count',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
