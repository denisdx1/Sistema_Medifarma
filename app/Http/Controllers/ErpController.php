<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\BusinessUnit;
use App\Models\Franchise;
use App\Models\Market;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ErpController extends Controller
{
    public function index(Request $request): View
    {
        // Start building the query for products
        $query = Product::with(['brand', 'franchise', 'businessUnit', 'market']);

        // Apply filters from the request
        if ($request->filled('franchise_id')) {
            $query->where('franchise_id', $request->franchise_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('business_unit_id')) {
            $query->where('business_unit_id', $request->business_unit_id);
        }
        if ($request->filled('market_id')) {
            $query->where('market_id', $request->market_id);
        }
        if ($request->filled('status')) {
            if ($request->status == 'with_code') {
                $query->whereNotNull('sku')->where('sku', '!=', '');
            } elseif ($request->status == 'without_code') {
                $query->where(function ($q) {
                    $q->whereNull('sku')->orWhere('sku', '');
                });
            }
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        // Paginate the results
        $products = $query->latest()->paginate(10)->withQueryString();

        // Data for filters and stats (can remain global for now)
        $franchises = Franchise::all();
        $brands = Brand::all();
        $businessUnits = BusinessUnit::all();
        $markets = Market::all();

        $totalProducts = Product::count();
        $productsWithoutCode = Product::whereNull('sku')->orWhere('sku', '')->count();
        $productsWithCode = Product::whereNotNull('sku')->where('sku', '!=', '')->count();

        return view('erp', compact(
            'products',
            'franchises',
            'brands',
            'businessUnits',
            'markets',
            'totalProducts',
            'productsWithoutCode',
            'productsWithCode'
        ));
    }
}