<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Placeholder extends Controller
{
    public function index($placeholderName)
    {
        return view('placeholders.template', ['placeholderName' => $placeholderName]);
    }
}
