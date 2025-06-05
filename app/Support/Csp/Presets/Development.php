<?php

namespace App\Support\Csp\Presets;

use Spatie\Csp\Directive;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class Development implements Preset
{
    public function configure(Policy $policy): void
    {
        /** @var string $appUrl */
        $appUrl = config('app.url');

        $appDomain = explode('://', $appUrl)[1];

        if (app()->isLocal()) {
            // Allow Vite to connect to the development server
            $policy
                ->add(Directive::CONNECT, ['wss://'.$appDomain.':5173']);
        }
    }
}
