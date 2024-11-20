<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Database extends Model
{
    use HasFactory;

    public $table = "databases";

    protected $fillable = [
        "connection_name",
        "driver",
        "host",
        "port",
        "name",
        "username",
        "password",
    ];
}
