<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MaterialInController extends Controller
{
    public function index()
    {
        return view('pages.material-in.index');
    }
}
