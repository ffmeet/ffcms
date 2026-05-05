<?php

namespace App\Support;

class OperationHistory
{
    public static function append(array $data, string $key, array $entry, int $limit = 8): array
    {
        $history = collect($data[$key] ?? [])
            ->filter(fn (mixed $item): bool => is_array($item))
            ->take($limit - 1)
            ->values()
            ->all();

        array_unshift($history, self::normalizeEntry($entry));

        $data[$key] = $history;

        return $data;
    }

    public static function makeEntry(string $event, string $source, ?string $status = null, array $context = []): array
    {
        return array_filter([
            'at' => now()->toDateTimeString(),
            'event' => $event,
            'source' => $source,
            'status' => $status,
            'note' => $context['note'] ?? null,
            'provider' => $context['provider'] ?? null,
            'order_no' => $context['order_no'] ?? null,
            'entry' => $context['entry'] ?? null,
        ], fn (mixed $value): bool => filled($value));
    }

    protected static function normalizeEntry(array $entry): array
    {
        $entry['at'] = $entry['at'] ?? now()->toDateTimeString();

        return $entry;
    }
}
