<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base Controller untuk seluruh controller aplikasi.
 * IMPORTANT: Jangan deklarasikan class Controller ini di file lain.
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}