<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TteController extends Controller
{
    public function index(Request $request)
    {
        // kalau view belum ada, pakai placeholder dulu
        return view('admin.tte.index');
    }
}