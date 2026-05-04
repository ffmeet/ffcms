<?php

namespace Tests\Unit;

use App\Support\UploadLogReader;
use PHPUnit\Framework\TestCase;

class UploadLogReaderTest extends TestCase
{
    public function test_it_can_parse_an_upload_log_line(): void
    {
        $entry = UploadLogReader::parseLine('[2026-04-09 04:24:28] local.INFO: member.cover.stored {"original_name":"demo.png","mime_type":"image/png","size":2370211,"user_id":2}');

        $this->assertNotNull($entry);
        $this->assertSame('2026-04-09 04:24:28', $entry['timestamp']);
        $this->assertSame('local', $entry['environment']);
        $this->assertSame('info', $entry['level']);
        $this->assertSame('member.cover.stored', $entry['event']);
        $this->assertSame('demo.png', $entry['context']['original_name']);
        $this->assertSame(2, $entry['context']['user_id']);
    }
}
