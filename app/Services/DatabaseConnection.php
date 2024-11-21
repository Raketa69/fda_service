<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;

class DatabaseConnection
{
    private Connection $connection;

    public function setConnection(string $connection_name): Connection
    {
        $this->connection = DB::connection($connection_name);

        return $this->connection;
    }

    public function getDatabaseTables(): array
    {
        if(!isset($this->connection))
        {
            throw new \Exception("Connection is not set.");
        }

        $tables = $this->connection->table('information_schema.tables')
            ->select('table_name')
            ->where('table_schema', 'public')
            ->get();

        return $tables->pluck('table_name')->toArray();
    }
}
