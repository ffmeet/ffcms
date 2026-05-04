<?php

namespace App\Support;

class OrderNumber
{
    public static function make(string $prefix = 'ORD'): string
    {
        return strtoupper($prefix) . now()->format('YmdHis') . random_int(1000, 9999);
    }
}
