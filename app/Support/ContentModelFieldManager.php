<?php

namespace App\Support;

use App\Models\ContentModel;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ContentModelFieldManager
{
    public const SUPPORTED_TYPES = [
        'text',
        'textarea',
        'number',
        'select',
        'toggle',
    ];

    public const RESERVED_NAMES = [
        'seo_title',
        'summary',
    ];

    /**
     * @param  array<int|string, mixed> | null  $fieldConfig
     * @return array<int, array{name: string, label: string, type: string, required: bool, options: array<int, string>}>
     */
    public static function normalizeFieldConfig(array|null $fieldConfig): array
    {
        if (blank($fieldConfig)) {
            return [];
        }

        if (Arr::isAssoc($fieldConfig)) {
            return collect($fieldConfig)
                ->map(function (mixed $value, string|int $key): array {
                    $name = static::sanitizeFieldName((string) $key);
                    $label = is_string($value) && filled($value) ? trim($value) : Str::headline((string) $key);

                    return [
                        'name' => $name,
                        'label' => $label,
                        'type' => 'text',
                        'required' => false,
                        'options' => [],
                    ];
                })
                ->filter(fn (array $field): bool => filled($field['name']))
                ->values()
                ->all();
        }

        return collect($fieldConfig)
            ->filter(fn (mixed $field): bool => is_array($field))
            ->map(function (array $field): array {
                $type = in_array($field['type'] ?? null, static::SUPPORTED_TYPES, true)
                    ? $field['type']
                    : 'text';

                $options = collect($field['options'] ?? [])
                    ->map(fn (mixed $option): string => trim((string) $option))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'name' => static::sanitizeFieldName((string) ($field['name'] ?? '')),
                    'label' => trim((string) ($field['label'] ?? '')),
                    'type' => $type,
                    'required' => (bool) ($field['required'] ?? false),
                    'options' => $type === 'select' ? $options : [],
                ];
            })
            ->filter(fn (array $field): bool => filled($field['name']) && filled($field['label']))
            ->unique('name')
            ->values()
            ->all();
    }

    public static function sanitizeFieldName(string $name): string
    {
        $name = Str::of($name)
            ->trim()
            ->lower()
            ->replaceMatches('/[^a-z0-9_]+/', '_')
            ->trim('_')
            ->value();

        if ($name === '' || in_array($name, static::RESERVED_NAMES, true)) {
            return '';
        }

        if (! preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
            return '';
        }

        return $name;
    }

    /**
     * @return array<int, string>
     */
    public static function getFieldNamesForModelId(int|string|null $modelId): array
    {
        if (blank($modelId)) {
            return [];
        }

        $model = ContentModel::query()->find($modelId);

        if (! $model) {
            return [];
        }

        return collect(static::normalizeFieldConfig($model->field_config))
            ->pluck('name')
            ->all();
    }

    /**
     * @param  array<string, mixed> | null  $customFields
     * @return array<string, mixed>
     */
    public static function filterCustomFieldsForModelId(array|null $customFields, int|string|null $modelId): array
    {
        $allowedFieldNames = static::getFieldNamesForModelId($modelId);

        if ($allowedFieldNames === []) {
            return [];
        }

        return Arr::only($customFields ?? [], $allowedFieldNames);
    }

    /**
     * @return array<int, Component>
     */
    public static function buildPostFormFieldsForModelId(int|string|null $modelId): array
    {
        if (blank($modelId)) {
            return [];
        }

        $model = ContentModel::query()->find($modelId);

        if (! $model) {
            return [];
        }

        return collect(static::normalizeFieldConfig($model->field_config))
            ->map(fn (array $field): Component => static::buildPostFormField($field))
            ->all();
    }

    /**
     * @param  array{name: string, label: string, type: string, required: bool, options: array<int, string>}  $field
     */
    protected static function buildPostFormField(array $field): Component
    {
        $statePath = "custom_fields.{$field['name']}";

        return match ($field['type']) {
            'textarea' => Textarea::make($statePath)
                ->label($field['label'])
                ->rows(4)
                ->required($field['required'])
                ->columnSpanFull()
                ->key("dynamic-field-{$field['name']}"),
            'number' => TextInput::make($statePath)
                ->label($field['label'])
                ->numeric()
                ->required($field['required'])
                ->key("dynamic-field-{$field['name']}"),
            'select' => Select::make($statePath)
                ->label($field['label'])
                ->options(array_combine($field['options'], $field['options']) ?: [])
                ->required($field['required'])
                ->native(false)
                ->key("dynamic-field-{$field['name']}"),
            'toggle' => Toggle::make($statePath)
                ->label($field['label'])
                ->default(false)
                ->required($field['required'])
                ->key("dynamic-field-{$field['name']}"),
            default => TextInput::make($statePath)
                ->label($field['label'])
                ->required($field['required'])
                ->key("dynamic-field-{$field['name']}"),
        };
    }
}
