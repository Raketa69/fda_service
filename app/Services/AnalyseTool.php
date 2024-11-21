<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Database;
use Illuminate\Support\Facades\DB;

class AnalyseTool
{
    public function __construct(
        private Database $database
    ) {}

    public function searchByKeys(): void
    {
        $query = "
            SELECT
                tc.table_name,
                kcu.column_name,
                ccu.table_name AS foreign_table_name,
                ccu.column_name AS foreign_column_name
            FROM
                information_schema.table_constraints AS tc
            JOIN
                information_schema.key_column_usage AS kcu
                ON tc.constraint_name = kcu.constraint_name
            JOIN
                information_schema.constraint_column_usage AS ccu
                ON ccu.constraint_name = tc.constraint_name
            WHERE
                constraint_type = 'FOREIGN KEY'
            ORDER BY
                tc.table_name, kcu.column_name;
        ";

        $result = DB::connection($this->database->connection_name)->query()
            ->raw($query);
    }
}
