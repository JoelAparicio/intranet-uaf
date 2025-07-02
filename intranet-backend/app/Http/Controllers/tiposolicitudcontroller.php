<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tipo_solicitud;

class TipoSolicitudController extends Controller
{
    public function listar_solicitudes()
    {
        $tiposSolicitud = tipo_solicitud::all();
        return response()->json($tiposSolicitud);
    }
}
