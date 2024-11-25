<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AlgorithmService;
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
}
