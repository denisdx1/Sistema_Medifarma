<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketAdministrationController extends Controller
{
    /**
     * Display pending markets for approval
     */
    public function index()
    {
        // Verificar que el usuario sea administrador
        if (!Auth::user()->isAdmin()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        try {
            // Obtener mercados pendientes con JOIN a la tabla de solicitudes
            $pendingMarkets = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select('m.*', 's.solicitud', 'e.estado')
                ->where('s.solicitud', 'ESPERA')
                ->orderBy('m.fechaRegistro', 'desc')
                ->get();

            // Obtener todos los estados disponibles
            $estados = DB::connection('sqlsrv')
                ->table('ODS.TAB_ESTADO')
                ->get();

            // Obtener todas las solicitudes disponibles
            $solicitudes = DB::connection('sqlsrv')
                ->table('ODS.TAB_SOLICITUD')
                ->get();

            return view('market-administration.index', compact('pendingMarkets', 'estados', 'solicitudes'));

        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar mercados pendientes: ' . $e->getMessage());
        }
    }

    /**
     * Approve a market
     */
    public function approve(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para aprobar mercados'
            ], 403);
        }

        $request->validate([
            'market_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();

            // Obtener el ID de solicitud para "APROBADO"
            $aprobadoSolicitud = DB::connection('sqlsrv')
                ->table('ODS.TAB_SOLICITUD')
                ->where('solicitud', 'APROBADO')
                ->first();

            if (!$aprobadoSolicitud) {
                throw new \Exception('No se encontró el estado APROBADO en la tabla de solicitudes');
            }

            // Obtener el mercado antes de actualizarlo
            $market = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->first();

            // Actualizar el mercado para cambiar idSolicitud de ESPERA a APROBADO
            DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->update([
                    'idSolicitud' => $aprobadoSolicitud->idSolicitud
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "El mercado '{$market->mercado}' ha sido aprobado exitosamente",
                'market_name' => $market->mercado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending markets count for dashboard
     */
    public function getPendingCount()
    {
        try {
            $count = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
                ->where('s.solicitud', 'ESPERA')
                ->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener conteo de mercados pendientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change market status (active/inactive)
     */
    public function changeStatus(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para cambiar el estado de mercados'
            ], 403);
        }

        $request->validate([
            'market_id' => 'required|integer',
            'status_id' => 'required|integer|exists:sqlsrv.ODS.TAB_ESTADO,idEstado'
        ]);

        try {
            DB::beginTransaction();

            // Actualizar el estado del mercado
            DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->update([
                    'idEstado' => $request->status_id,
                    'fechaUpdate' => now(),
                    'idUsuario' => Auth::id()
                ]);

            // Obtener el nombre del mercado y el nuevo estado
            $market = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->first();

            $estado = DB::connection('sqlsrv')
                ->table('ODS.TAB_ESTADO')
                ->where('idEstado', $request->status_id)
                ->first();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "El estado del mercado '{$market->mercado}' ha sido cambiado a '{$estado->estado}'",
                'market_name' => $market->mercado,
                'new_status' => $estado->estado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del mercado: ' . $e->getMessage()
            ], 500);
        }
    }
}
