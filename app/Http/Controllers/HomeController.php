<?php

namespace App\Http\Controllers;

use NckRtl\RouteMaker\Route;

class HomeController extends Controller
{
    #[Route(uri: '/')]
    public function show(): \Inertia\ResponseFactory|\Inertia\Response
    {
        return inertia('Home');
    }
}
