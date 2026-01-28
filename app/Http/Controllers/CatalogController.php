<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Category;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Display a listing of the resources for browsing with filtering and pagination.
     */
    public function browse(Request $request)
    {
        $query = Resource::query();

        // Filter by Category
        if ($request->has('categories') && is_array($request->categories)) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->whereIn('name', $request->categories);
            });
        }

        // Filter by Status
        if ($request->has('statuses') && is_array($request->statuses)) {
            $query->whereIn('status', $request->statuses);
        }

        // Sorting Logic
        $sort = $request->input('sort', 'category_asc');
        
        // We need to join categories for sorting by its name
        $query->leftJoin('categories', 'resources.category_id', '=', 'categories.id')
            ->select('resources.*');

        switch ($sort) {
            case 'name_asc':
                $query->orderBy('resources.name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('resources.name', 'desc');
                break;
            case 'status_asc':
                $query->orderBy('resources.status', 'asc');
                break;
            case 'category_desc':
                $query->orderBy('categories.name', 'desc')->orderBy('resources.name', 'asc');
                break;
            default: // category_asc
                $query->orderBy('categories.name', 'asc')->orderBy('resources.name', 'asc');
                break;
        }

        $resources = $query->with('category')->paginate(24)
            ->withQueryString();

        $categories = Category::all();

        return view('catalog.catalog', compact('resources', 'categories'));
    }
}