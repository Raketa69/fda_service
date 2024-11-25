<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Algorithm;
use Illuminate\Database\Eloquent\Collection;

class AlgorithmService
{
    public function getList(): Collection
    {
        return Algorithm::get();
    }
}
