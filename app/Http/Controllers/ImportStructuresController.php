<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StructuresImport;


class ImportStructuresController extends Controller
{
    public function importStructures(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new StructuresImport, $request->file('file'));

        return response()->json(['message' => 'Structures importées avec succès !']);
    }
}
