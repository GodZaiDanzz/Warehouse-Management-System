<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DebuggingController extends Controller
{
    public function resetIndex()
    {
        return view('pages.debugging.reset');
    }
}
