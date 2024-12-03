<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\DTO\DatabaseConnectionDTO;
use App\Services\AnalyzeService;
use App\Services\DatabaseManager;
use App\Services\SimpleSearch\SearchService;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('start', function () {

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
    $connection = $dbmanager->getDatabaseConnection("dvdrental");

    $ss = new SearchService();
    $dependecies = $ss->findFunctionalDependencies($connection);

    // dd($dependecies);

    $as = (new AnalyzeService($connection));
    $violations = $as->analyzeNormalForms($dependecies);



    $result = [];
    foreach ($violations as $value) {
        $result[] = $as->analyzeDependencies($value["dependencies"], $value['table']);
    }

    dd(...$result);
});
