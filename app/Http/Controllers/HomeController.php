<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function show()
    {
        return inertia('Home');
    }
}
