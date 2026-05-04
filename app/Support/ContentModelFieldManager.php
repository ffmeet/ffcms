<?php

namespace App\Support;

use App\Models\ContentModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
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
     * @return array<int, array{name: string, label: string, type: string, required: bool, options: array<int, string>, placeholder: string, helper_text: string, default: mixed}>
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
                        'placeholder' => '',
                        'helper_text' => '',
                        'default' => null,
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
                    'placeholder' => trim((string) ($field['placeholder'] ?? '')),
                    'helper_text' => trim((string) ($field['helper_text'] ?? '')),
                    'default' => static::normalizeDefaultValue($type, $field['default'] ?? null, $options),
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
     * @param  array{name: string, label: string, type: string, required: bool, options: array<int, string>, placeholder: string, helper_text: string, default: mixed}  $field
     */
    protected static function buildPostFormField(array $field): Component
    {
        $statePath = "custom_fields.{$field['name']}";

        return match ($field['type']) {
            'textarea' => Textarea::make($statePath)
                ->label($field['label'])
                ->rows(4)
                ->placeholder($field['placeholder'])
                ->default($field['default'])
                ->required($field['required'])
                ->inlineLabel()
                ->columnSpanFull()
                ->key("dynamic-field-{$field['name']}"),
            'number' => TextInput::make($statePath)
                ->label($field['label'])
                ->numeric()
                ->placeholder($field['placeholder'])
                ->default($field['default'])
                ->required($field['required'])
                ->inlineLabel()
                ->key("dynamic-field-{$field['name']}"),
            'select' => Select::make($statePath)
                ->label($field['label'])
                ->options(array_combine($field['options'], $field['options']) ?: [])
                ->default($field['default'])
                ->required($field['required'])
                ->native(false)
                ->inlineLabel()
                ->key("dynamic-field-{$field['name']}"),
            'toggle' => Toggle::make($statePath)
                ->label($field['label'])
                ->default($field['default'] ?? false)
                ->required($field['required'])
                ->inlineLabel(false)
                ->key("dynamic-field-{$field['name']}"),
            default => TextInput::make($statePath)
                ->label($field['label'])
                ->placeholder($field['placeholder'])
                ->default($field['default'])
                ->required($field['required'])
                ->inlineLabel()
                ->key("dynamic-field-{$field['name']}"),
        };
    }

    /**
     * @param  array<int, string>  $options
     */
    protected static function normalizeDefaultValue(string $type, mixed $default, array $options): mixed
    {
        if ($default === null || $default === '') {
            return $type === 'toggle' ? false : null;
        }

        return match ($type) {
            'number' => is_numeric($default) ? $default + 0 : null,
            'toggle' => filter_var($default, FILTER_VALIDATE_BOOL) || $default === true || $default === 1 || $default === '1',
            'select' => in_array((string) $default, $options, true) ? (string) $default : null,
            default => trim((string) $default),
        };
    }
}
