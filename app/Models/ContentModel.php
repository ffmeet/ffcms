<?php

namespace App\Models;

use App\Support\ContentModelFieldManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'table_name',
        'field_config',
    ];

    protected function casts(): array
    {
        return [
            'field_config' => 'array',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'model_id');
    }

    /**
     * @return array<int, array{name: string, label: string, type: string, required: bool, options: array<int, string>, placeholder: string, helper_text: string, default: mixed}>
     */
    public function normalizedFieldConfig(): array
    {
        return ContentModelFieldManager::normalizeFieldConfig($this->field_config);
    }
}
