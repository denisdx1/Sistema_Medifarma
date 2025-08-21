<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Log; // Add this line
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Add this line

class ProductController extends Controller
{
    /**
     * Generate and assign a new SKU for a product.
     */
    public function generateAndAssignSku(Product $product)
    {
        // Ensure SKU is not already set, to prevent overwriting
        if ($product->sku) {
            return redirect()->route('market-management.index')->with('error', 'Este producto ya tiene un SKU asignado.');
        }

        // Generate a unique SKU
        $baseSku = strtoupper(substr($product->brand->name, 0, 3)) . '-' . strtoupper(substr($product->market->name, 0, 3)) . '-' . $product->id;
        $sku = $baseSku;
        $counter = 1;
        while (Product::where('sku', $sku)->exists()) {
            $sku = $baseSku . '-' . $counter++;
        }

        $product->update(['sku' => $sku]);

        // Log the action
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'generated_sku',
            'model_type' => Product::class,
            'model_id' => $product->id,
            'old_value' => ['sku' => null], // Assuming SKU was null before
            'new_value' => ['sku' => $sku],
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);

        return redirect()->route('market-management.index')->with('success', 'Nuevo SKU (' . $sku . ') asignado correctamente.');
    }
}
