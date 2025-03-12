<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmailService;

class EmailController extends Controller
{
    public function sendEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'message' => 'required|string'
        ]);

        EmailService::sendEmail($request->email, $request->message);

        return response()->json(['message' => 'Email envoyé avec succès !'], 200);
    }
}
