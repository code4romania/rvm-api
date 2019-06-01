<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    /**
     * @SWG\Swagger(
     *   basePath="/",
     *   @SWG\Info(
     *     title="RVM API",
     *     version="1.0.0"
     *   )
     * )
     */
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
