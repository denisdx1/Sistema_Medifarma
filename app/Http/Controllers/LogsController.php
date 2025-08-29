<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LogsController extends Controller
{
    /**
     * Limpiar texto del detalle de logs
     */
    private function cleanLogDetail($detail)
    {
        if (empty($detail)) {
            return '';
        }
        
        // Eliminar espacios extra al inicio y final
        $detail = trim($detail);
        
        // Reemplazar múltiples espacios con uno solo
        $detail = preg_replace('/\s+/', ' ', $detail);
        
        // Reemplazar saltos de línea múltiples con uno solo
        $detail = preg_replace('/\n\s*\n/', "\n", $detail);
        
        // Limpiar espacios alrededor de caracteres especiales
        $detail = preg_replace('/\s*([|?])\s*/', '$1 ', $detail);
        
        return $detail;
    }

    /**
     * Display market logs table (only for administrators)
     */
    public function index(Request $request)
    {
        // Verificar que el usuario sea administrador
        if (Auth::user()->idRol !== 1) { // Asumiendo que idRol 1 = administrador
            abort(403, 'Acceso denegado. Solo administradores pueden ver los logs.');
        }

        $perPage = 10; // Registros por página
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        // Filtros
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $usuario = $request->get('usuario');
        $accion = $request->get('accion');
        $search = $request->get('search');

        try {
            // Query base para los logs
            $query = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO_LOG')
                ->select([
                    'id',
                    'usuario',
                    'accion',
                    'fecha',
                    'detalle',
                    'tabla',
                    'nota'  // Agregar campo nota
                ])
                ->orderBy('fecha', 'desc')
                ->orderBy('id', 'desc');

            // Aplicar filtros
            if ($dateFrom) {
                $query->whereDate('fecha', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('fecha', '<=', $dateTo);
            }

            if ($usuario) {
                $query->where('usuario', 'like', '%' . $usuario . '%');
            }

            if ($accion) {
                $query->where('accion', $accion);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('detalle', 'like', '%' . $search . '%')
                      ->orWhere('usuario', 'like', '%' . $search . '%')
                      ->orWhere('tabla', 'like', '%' . $search . '%')
                      ->orWhere('nota', 'like', '%' . $search . '%');  // Incluir nota en búsqueda
                });
            }

            // Obtener total de registros para paginación
            $totalRecords = $query->count();
            $totalPages = ceil($totalRecords / $perPage);

            // Obtener los registros con paginación
            $logs = $query->offset($offset)->limit($perPage)->get();
            
            // Limpiar el detalle de cada log
            $logs->each(function($log) {
                $log->detalle = $this->cleanLogDetail($log->detalle);
                $log->nota = $this->cleanLogDetail($log->nota);  // Limpiar también la nota
            });

            // Obtener opciones únicas para filtros
            $usuarios = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO_LOG')
                ->select('usuario')
                ->distinct()
                ->whereNotNull('usuario')
                ->where('usuario', '!=', '')
                ->orderBy('usuario')
                ->pluck('usuario');

            $acciones = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO_LOG')
                ->select('accion')
                ->distinct()
                ->whereNotNull('accion')
                ->where('accion', '!=', '')
                ->orderBy('accion')
                ->pluck('accion');

            return view('logs.index', compact(
                'logs',
                'usuarios',
                'acciones',
                'totalRecords',
                'totalPages',
                'page',
                'perPage',
                'dateFrom',
                'dateTo',
                'usuario',
                'accion',
                'search'
            ));

        } catch (\Exception $e) {
            \Log::error('Error al cargar logs de mercado', [
                'error' => $e->getMessage(),
                'user' => Auth::user()->usuario
            ]);

            return back()->with('error', 'Error al cargar los logs: ' . $e->getMessage());
        }
    }

    /**
     * Export logs to CSV (for administrators)
     */
    public function export(Request $request)
    {
        // Verificar que el usuario sea administrador
        if (Auth::user()->idRol !== 1) {
            abort(403, 'Acceso denegado.');
        }

        // Aplicar los mismos filtros que en index
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $usuario = $request->get('usuario');
        $accion = $request->get('accion');
        $search = $request->get('search');

        try {
            $query = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO_LOG')
                ->select([
                    'id',
                    'usuario',
                    'accion',
                    'fecha',
                    'detalle',
                    'tabla',
                    'nota'  // Agregar campo nota
                ])
                ->orderBy('fecha', 'desc');

            // Aplicar filtros
            if ($dateFrom) {
                $query->whereDate('fecha', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('fecha', '<=', $dateTo);
            }

            if ($usuario) {
                $query->where('usuario', 'like', '%' . $usuario . '%');
            }

            if ($accion) {
                $query->where('accion', $accion);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('detalle', 'like', '%' . $search . '%')
                      ->orWhere('usuario', 'like', '%' . $search . '%')
                      ->orWhere('tabla', 'like', '%' . $search . '%')
                      ->orWhere('nota', 'like', '%' . $search . '%');  // Incluir nota en búsqueda
                });
            }

            $logs = $query->get();

            $filename = 'logs_mercado_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($logs) {
                $file = fopen('php://output', 'w');
                
                // UTF-8 BOM para Excel
                fwrite($file, "\xEF\xBB\xBF");
                
                // Headers
                fputcsv($file, ['ID', 'Usuario', 'Acción', 'Fecha', 'Detalle', 'Tabla', 'Nota'], ';');

                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->usuario,
                        $log->accion,
                        $log->fecha,
                        $log->detalle,
                        $log->tabla,
                        $log->nota
                    ], ';');
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            \Log::error('Error al exportar logs', [
                'error' => $e->getMessage(),
                'user' => Auth::user()->usuario
            ]);

            return back()->with('error', 'Error al exportar logs: ' . $e->getMessage());
        }
    }

    /**
     * Get logs statistics for dashboard
     */
    public function getStats(Request $request)
    {
        // Verificar que el usuario sea administrador
        if (Auth::user()->idRol !== 1) {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        try {
            $dateFrom = $request->get('date_from', date('Y-m-d', strtotime('-30 days')));
            $dateTo = $request->get('date_to', date('Y-m-d'));

            // Estadísticas por acción
            $actionStats = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO_LOG')
                ->select('accion', DB::raw('COUNT(*) as count'))
                ->whereDate('fecha', '>=', $dateFrom)
                ->whereDate('fecha', '<=', $dateTo)
                ->groupBy('accion')
                ->orderBy('count', 'desc')
                ->get();

            // Estadísticas por usuario
            $userStats = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO_LOG')
                ->select('usuario', DB::raw('COUNT(*) as count'))
                ->whereDate('fecha', '>=', $dateFrom)
                ->whereDate('fecha', '<=', $dateTo)
                ->groupBy('usuario')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // Actividad por día
            $dailyActivity = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO_LOG')
                ->select(DB::raw('CAST(fecha AS DATE) as date'), DB::raw('COUNT(*) as count'))
                ->whereDate('fecha', '>=', $dateFrom)
                ->whereDate('fecha', '<=', $dateTo)
                ->groupBy(DB::raw('CAST(fecha AS DATE)'))
                ->orderBy('date', 'asc')
                ->get();

            return response()->json([
                'action_stats' => $actionStats,
                'user_stats' => $userStats,
                'daily_activity' => $dailyActivity
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener estadísticas de logs', [
                'error' => $e->getMessage(),
                'user' => Auth::user()->usuario
            ]);

            return response()->json(['error' => 'Error al obtener estadísticas'], 500);
        }
    }
}
