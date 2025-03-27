<?php

use function Spatie\RouteTesting\routeTesting;

routeTesting('all public routes')
    ->include([
        '/',
    ])
    ->followingRedirects()
    ->assertSuccessful();
