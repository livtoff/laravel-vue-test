<?php

namespace App\Support\Csp\Presets;

use Spatie\Csp\Directive;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class Development implements Preset
{
    public function configure(Policy $policy): void
    {
        $appDomain = explode('://', config('app.url'))[1];

        if (app()->isLocal()) {
            // Allow Vite to connect to the development server
            $policy
                ->add(Directive::CONNECT, ['wss://'.$appDomain.':5173']);
        }
    }
}
