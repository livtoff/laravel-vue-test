<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HandleInertiaResponse
{
    private Request $request;

    private Response $response;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->request = $request;

        $this->response = $next($this->request);

        if (! $this->isInitialInertiaResponse() && ! $this->isInertiaResponse()) {
            return $next($this->request);
        }

        $inertiaProperties = $this->getInertiaProperties();

        if (! is_array($inertiaProperties)) {
            return $this->response;
        }

        $pageProps = $inertiaProperties['props'];

        $pageComponentProps = $this->getPageComponentProps($pageProps);

        $this->request->attributes->set('telescope_inertia_shared', [
            'rootView' => '/resources/views/'.(new HandleInertiaRequests)->rootView($this->request).'.blade.php',
            'versions' => [
                'inertiajs/inertia-laravel' => 'v2.0.2',
                '@inertiajs/core' => 'v2.0.11',
                '@inertiajs/vue3' => 'v2.0.11',
                'Assets' => $inertiaProperties['version'],
            ],
            'page' => [
                'props' => $pageProps,
                'component' => $inertiaProperties['component'],
                'component_path' => gettype($inertiaProperties['component']) === 'string' ? base_path(sprintf('resources/js/pages/%s.vue', $inertiaProperties['component'])) : null,
                'component_props' => $pageComponentProps,
            ],
            'clearHistory' => $inertiaProperties['clearHistory'],
            'encryptHistory' => $inertiaProperties['encryptHistory'],
        ]);

        return $this->response;
    }

    private function isInitialInertiaResponse(): bool
    {
        if (! $this->response->getOriginalContent() instanceof View) {
            return false;
        }

        return $this->response->getOriginalContent()->getName() == (new HandleInertiaRequests)->rootView($this->request);
    }

    private function isInertiaResponse(): bool
    {
        return $this->response->headers->get('x-inertia') === 'true';
    }

    private function getInertiaProperties(): mixed
    {
        $originalContent = $this->response->getOriginalContent();

        if ($originalContent instanceof View) {
            return $originalContent->getData()['page'];
        }

        return $originalContent;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getPageComponentProps(mixed $pageProps): ?array
    {
        if (! is_array($pageProps)) {
            return null;
        }

        $sharedProps = (new HandleInertiaRequests)->share($this->request);

        return array_filter($pageProps, fn ($key): bool => ! in_array($key, array_keys($sharedProps)), ARRAY_FILTER_USE_KEY);
    }
}
