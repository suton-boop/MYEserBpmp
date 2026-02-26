<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AuditController extends Controller
{
    public function index()
    {
        return view('admin.audit.index');
    }
}
