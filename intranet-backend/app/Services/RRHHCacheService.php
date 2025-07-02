<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RRHHCacheService
{
    const CACHE_KEY = 'usuarios_rrhh_con_firma';
    const CACHE_TTL = 3600; // 1 hora

    /**
     * Obtener usuarios RRHH con firma (con cache)
     */
    public static function getUsuariosRRHHConFirma()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            Log::info('Generando cache de usuarios RRHH con firma');

            return User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['Recursos Humanos', 'Jefe de Recursos Humanos']);
            })
                ->whereNotNull('firma_path')
                ->where('firma_path', '!=', '')
                ->select('id', 'nombre', 'firma_path')
                ->get();
        });
    }

    /**
     * Buscar la primera firma RRHH disponible
     */
    public static function buscarPrimeraFirmaRRHH()
    {
        $usuariosRRHH = self::getUsuariosRRHHConFirma();

        foreach ($usuariosRRHH as $rrhh) {
            if ($rrhh->firma_path && file_exists(public_path('storage/' . $rrhh->firma_path))) {
                Log::info('Firma RRHH encontrada en cache', [
                    'usuario_id' => $rrhh->id,
                    'usuario_nombre' => $rrhh->nombre,
                    'firma_path' => $rrhh->firma_path
                ]);
                return $rrhh->firma_path;
            }
        }

        Log::warning('No se encontró ninguna firma RRHH válida en cache');
        return null;
    }

    /**
     * Buscar firma de un usuario RRHH específico
     */
    public static function buscarFirmaRRHHEspecifico($userId)
    {
        if (!$userId) {
            return null;
        }

        // Buscar en cache primero
        $usuariosRRHH = self::getUsuariosRRHHConFirma();
        $usuarioEnCache = $usuariosRRHH->firstWhere('id', $userId);

        if ($usuarioEnCache && $usuarioEnCache->firma_path && file_exists(public_path('storage/' . $usuarioEnCache->firma_path))) {
            Log::info('Firma RRHH específica encontrada en cache', [
                'usuario_id' => $userId,
                'firma_path' => $usuarioEnCache->firma_path
            ]);
            return $usuarioEnCache->firma_path;
        }

        // Si no está en cache o no tiene firma válida, buscar directamente
        $usuario = User::find($userId);
        if ($usuario && $usuario->firma_path && file_exists(public_path('storage/' . $usuario->firma_path))) {
            Log::info('Firma RRHH específica encontrada directamente (no en cache)', [
                'usuario_id' => $userId,
                'firma_path' => $usuario->firma_path
            ]);
            return $usuario->firma_path;
        }

        return null;
    }

    /**
     * Invalidar cache de usuarios RRHH
     */
    public static function invalidarCache($motivo = 'manual')
    {
        $resultado = Cache::forget(self::CACHE_KEY);

        Log::info('Cache de usuarios RRHH invalidado', [
            'motivo' => $motivo,
            'resultado' => $resultado ? 'exitoso' : 'fallido'
        ]);

        return $resultado;
    }

    /**
     * Verificar si el cache existe
     */
    public static function existeCache()
    {
        return Cache::has(self::CACHE_KEY);
    }

    /**
     * Obtener información del cache
     */
    public static function infoCacheParaDebug()
    {
        $existe = self::existeCache();
        $usuarios = $existe ? self::getUsuariosRRHHConFirma() : collect();

        return [
            'cache_existe' => $existe,
            'usuarios_en_cache' => $usuarios->count(),
            'usuarios_con_firma_valida' => $usuarios->filter(function ($user) {
                return $user->firma_path && file_exists(public_path('storage/' . $user->firma_path));
            })->count(),
            'usuarios_detalles' => $usuarios->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'firma_path' => $user->firma_path,
                    'archivo_existe' => $user->firma_path ? file_exists(public_path('storage/' . $user->firma_path)) : false
                ];
            })
        ];
    }
}
