<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Birthday;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BirthdayController extends Controller
{
    public function getBirthdays()
    {
        $birthdays = Birthday::with('departmentos')->where('deleted', 0)->get();

        $events = $birthdays->map(function($birthday) {
            return [
                'id' => $birthday->id_birthday,
                'nombre' => $birthday->usuario,
                'departamento' => $birthday->departmentos->nombre,
                'birthday' => $birthday->fecha_birthday,
            ];
        });

        return response()->json($events);
    }

    public function addBirthday(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'departamento' => 'required|integer|exists:departamentos,id_departamento',
        'birthday' => 'required|date_format:Y-m-d',
    ]);

    $birthday = new Birthday();
    $birthday->usuario = $request->input('nombre');
    $birthday->id_departamento = $request->input('departamento');
    $birthday->fecha_birthday = $request->input('birthday');
    $birthday->deleted = 0;
    $birthday->save();

    return response()->json(['success' => true, 'message' => 'Cumpleaños agregado exitosamente']);
}


    public function deleteBirthday($id, Request $request)
    {
        $birthday = Birthday::find($id);
        if ($birthday) {
            $birthday->delete();
            $birthday->deleted = 1;
            $birthday->save();

            return response()->json(['success' => true, 'message' => 'Cumpleaños eliminado exitosamente']);
        }

        return response()->json(['success' => false, 'message' => 'Cumpleaños no encontrado'], 404);
    }
}
