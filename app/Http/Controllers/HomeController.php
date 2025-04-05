<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function show(): \Inertia\ResponseFactory|\Inertia\Response
    {
        return inertia('Home');
    }
}
