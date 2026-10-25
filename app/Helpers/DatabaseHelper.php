<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class DatabaseHelper
{
    public static function isColumnNullable(string $tableName, string $columnName): bool
    {
        $column = collect(DB::select("SHOW COLUMNS FROM {$tableName} WHERE Field = ?", [$columnName]))->first();
        
        if (!$column) {
            throw new \Exception("Column '{$columnName}' not found in table '{$tableName}'");
        }
        
        return $column->Null === 'YES';
    }
}