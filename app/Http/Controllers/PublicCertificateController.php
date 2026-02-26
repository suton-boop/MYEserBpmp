<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicCertificateController extends Controller
{
    public function search(Request $request)
    {
        // sementara, supaya route:list tidak error
        return back()->with('error', 'Fitur pencarian publik belum diimplementasikan.');
    }
}