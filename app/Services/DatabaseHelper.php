<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;

class DatabaseHelper
{
    public function __construct(
        private Connection $connection
    ) {}

    public function getDatabaseTables(): array
    {
        if (!isset($this->connection)) {
            throw new \Exception("Connection is not set.");
        }

        $tables = DB::connection($this->connection->getName())
            ->table('information_schema.tables')
            ->select('table_name')
            ->where('table_schema', 'public')
            ->get();

        return $tables->pluck('table_name')->toArray();
    }
}
