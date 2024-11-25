<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Database;
use Illuminate\Database\Eloquent\Collection;

class DatabaseService
{
    public function getDbList(): Collection
    {
        return Database::get();
    }
}
