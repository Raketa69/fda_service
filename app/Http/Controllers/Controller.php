<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\DatabaseConnectionDTO;
use App\Services\AlgorithmService;
use App\Services\DatabaseManager;
use App\Services\DatabaseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\View\View;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct(
        private DatabaseService $databaseService,
        private AlgorithmService $algorithmService
    ) {}

    public function getMainPage(): View
    {
        $databases = $this->databaseService->getDbList()->pluck("name");
        $algs = $this->algorithmService->getList()->pluck("name");

        return view('main', compact(['databases', 'algs']));
    }

    public function addDatabaseConnection($request): mixed
    {

        $dto = new DatabaseConnectionDTO();

        $dto->connection_name = $request->connection_name;
        $dto->driver          = $request->driver;
        $dto->host            = $request->host;
        $dto->port            = $request->port;
        $dto->database        = $request->database;
        $dto->username        = $request->username;
        $dto->password        = $request->password;

        $dbmanager = new DatabaseManager();
        $dbmanager->addDatabaseConnection($dto);

        return response("ok", 200);
    }

    public function uploadFile($request): mixed
    {
        return response("ok", 200);
    }

    public function analyze($request): mixed
    {
        return response("ok", 200);
    }

    public function results($request): mixed
    {
        return response("ok", 200);
    }

    public function search($request): mixed
    {
        return response("ok", 200);
    }

    public function download($request): mixed
    {
        return response("ok", 200);
    }
}
