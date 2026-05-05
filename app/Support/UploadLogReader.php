<?php

namespace App\Support;

use Illuminate\Support\Collection;

class UploadLogReader
{
    public static function recentEntries(
        int $limit = 100,
        ?string $level = null,
        ?string $search = null,
    ): Collection {
        $entries = collect();

        foreach (self::logFiles() as $filePath) {
            $lines = @file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (! is_array($lines)) {
                continue;
            }

            foreach (array_reverse($lines) as $line) {
                $entry = self::parseLine($line);

                if (! $entry) {
                    continue;
                }

                if ($level && $entry['level'] !== strtolower($level)) {
                    continue;
                }

                if ($search && ! self::entryMatchesSearch($entry, $search)) {
                    continue;
                }

                $entries->push($entry);

                if ($entries->count() >= $limit) {
                    return $entries;
                }
            }
        }

        return $entries;
    }

    public static function summary(): array
    {
        $entries = self::recentEntries(200);

        return [
            'total' => $entries->count(),
            'errors' => $entries->where('level', 'error')->count(),
            'warnings' => $entries->where('level', 'warning')->count(),
            'latest_failed_at' => $entries
                ->first(fn (array $entry): bool => in_array($entry['level'], ['error', 'warning'], true))['timestamp'] ?? null,
        ];
    }

    public static function parseLine(string $line): ?array
    {
        $pattern = '/^\[(?<timestamp>[^\]]+)\]\s+(?<environment>[a-zA-Z0-9_-]+)\.(?<level>[A-Z]+):\s+(?<event>[^\s]+)\s+(?<context>\{.*\})\s*$/';

        if (! preg_match($pattern, $line, $matches)) {
            return null;
        }

        $context = json_decode($matches['context'], true);

        if (! is_array($context)) {
            $context = [];
        }

        return [
            'timestamp' => $matches['timestamp'],
            'environment' => $matches['environment'],
            'level' => strtolower($matches['level']),
            'event' => $matches['event'],
            'context' => $context,
        ];
    }

    protected static function logFiles(): array
    {
        $files = glob(storage_path('logs/upload-*.log')) ?: [];

        rsort($files);

        return $files;
    }

    protected static function entryMatchesSearch(array $entry, string $search): bool
    {
        $haystack = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (! is_string($haystack)) {
            return false;
        }

        return str_contains(mb_strtolower($haystack), mb_strtolower($search));
    }
}
