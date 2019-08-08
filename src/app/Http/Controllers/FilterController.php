<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Resource;
use App\Organisation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class FilterController extends Controller
{
    public function filterResourcesTypeName(Request $request)
    {
        $filters = Resource::query()
            ->get(['type_name'])
            ->unique('type_name')
            ->values()
            ->all();

        return response()->json($filters, 200);
    }

    public function filterOrganisationsName(Request $request)
    {
        $filters = Organisation::query()
            ->get(['name'])
            ->unique('name')
            ->values()
            ->all();

        return response()->json($filters, 200);
    }
}
