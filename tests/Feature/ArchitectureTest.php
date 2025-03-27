<?php

arch('No debugging calls are used')
    ->expect(['dd', 'dump'])
    ->not->toBeUsed();

arch()->preset()->laravel();
