<?php

namespace App\Http\Controllers;

use App\Models\VmaeProductoIqvia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MarketConfigurationController extends Controller
{
    /**
     * Constructor - Verificar permisos de usuario
     */
    public function __construct()
    {
        // El middleware se maneja en las rutas, no aquí
    }

    /**
     * Mostrar la página principal con los productos IQVIA
     */
    public function index(Request $request)
    {
        // Verificar permisos
        if (!Auth::user()->isAdmin() && !Auth::user()->isProductManager() && !Auth::user()->isBusinessIntelligence()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return view('market-configuration.index');
    }

    /**
     * Obtener productos IQVIA con cursor pagination
     */
    public function getProductos(Request $request)
    {
        // Verificar permisos
        if (!Auth::user()->isAdmin() && !Auth::user()->isProductManager() && !Auth::user()->isBusinessIntelligence()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para acceder a esta sección.'
            ], 403);
        }

        try {
            // Tamaño de página (máximo 50)
            $perPage = min((int) $request->get('per_page', 10), 50);
            
            // Obtener cursor de la request
            $cursor = $request->get('cursor');

            // Query base con solo las columnas necesarias
            $query = VmaeProductoIqvia::select([
                'Código_Presentación',
                'Descripción_Presentación',
                'Marca_Genérico',
                'Ético_Popular',
                'Molécula',
                'Código_FF_3',
                'Código_ATC_4',
                'Descripción_Laboratorio',
                'MERCADO'
            ])->orderBy('Descripción_Presentación');

            // Aplicar cursor si existe
            if ($cursor) {
                $productos = $query->cursorPaginate($perPage, ['*'], 'cursor', $cursor);
            } else {
                $productos = $query->cursorPaginate($perPage);
            }

            return response()->json([
                'success' => true,
                'data' => $productos->items(),
                'pagination' => [
                    'per_page'           => $productos->perPage(),
                    'next_cursor'        => $productos->nextCursor()?->encode(),
                    'prev_cursor'        => $productos->previousCursor()?->encode(),
                    'has_more_pages'     => $productos->hasMorePages(),
                    'has_previous_pages' => $productos->previousCursor() !== null,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en getProductos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ], 500);
        }
    }
}
