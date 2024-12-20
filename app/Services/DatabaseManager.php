<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\DatabaseConnectionDTO;
use App\Models\Database;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DatabaseManager
{
    public function addDatabaseConnection(DatabaseConnectionDTO $dto): string
    {
        Database::create([
            'connection_name' => $dto->connection_name,
            'driver'          => $dto->driver,
            'host'            => $dto->host,
            'port'            => $dto->port,
            'name'            => $dto->database,
            'username'        => $dto->username,
            'password'        => $dto->password,
        ]);

        Config::set("database.connections.$dto->connection_name", value: [
            'driver'   => $dto->driver,
            'host'     => $dto->host,
            'port'     => $dto->port,
            'database' => $dto->database,
            'username' => $dto->username,
            'password' => $dto->password,
        ]);

        return $dto->connection_name;
    }

    public function getDatabaseConnection(string $connection_name): Connection
    {
        return DB::connection($connection_name);
    }

    public function deleteDatabaseConnection(string $connection_name): void
    {
        $database = Database::where("connection_name", $connection_name)->firstOrFail();

        $database->deleteOrFail();
    }
}
