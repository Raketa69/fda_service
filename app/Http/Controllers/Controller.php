<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\DatabaseConnectionDTO;
use App\Services\AlgorithmService;
use App\Services\AnalyzeService;
use App\Services\DatabaseManager;
use App\Services\DatabaseService;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\View\View;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    private AnalyzeService $analyzeService;
    public  Connection $connection;
    private DatabaseService $databaseService;
    private AlgorithmService $algorithmService;

    public function __construct()
    {
        $dto = new DatabaseConnectionDTO();

        $dto->connection_name = "dvdrental";
        $dto->driver = "pgsql";
        $dto->host = "pgsql";
        $dto->port = 5432;
        $dto->database = "dvdrental";
        $dto->username = "root";
        $dto->password = "secret";

        $dbmanager = new DatabaseManager();
        $dbmanager->addDatabaseConnection($dto);

        $this->connection = $dbmanager->getDatabaseConnection("dvdrental");

        $this->databaseService = new DatabaseService();
        $this->algorithmService = new AlgorithmService();
        $this->analyzeService = new AnalyzeService($this->connection);
    }

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

    public function analyze(): mixed
    {
        $data = $this->analyzeService->getMainAnalytic($this->connection);

        return response($data, 200);
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
