<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::with(['category', 'user'])
            ->whereIn('state', ['published', 'draft']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'ilike', '%' . $request->search . '%')
                    ->orWhere('alias', 'ilike', '%' . $request->search . '%');
            });
        }

        if ($request->state) {
            $query->where('state', $request->state);
        }

        $materials = $query->orderBy('id', 'desc')->paginate(10);

        return response()->json($materials);
    }

    public function getByAlias(string $alias)
    {
        $material = Material::where('alias', $alias)->firstOrFail();
        return response()->json($material);
    }
}
