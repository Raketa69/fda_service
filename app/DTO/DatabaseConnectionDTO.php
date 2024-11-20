<?php

declare(strict_types=1);

namespace App\DTO;

class DatabaseConnectionDTO 
{
    public string $connection_name;
    public string $driver;
    public string $host;
    public int    $port;
    public string $database;
    public string $username;
    public string $password;
}