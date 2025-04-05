<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateControllersTs extends Command
{
    protected $signature = 'generate:controllers-ts';

    protected $description = 'Generate a controllers.ts file exporting all default controller modules as named exports';

    public function handle(): int
    {
        $actionsPath = base_path('resources/js/actions');
        $outputPath = base_path('resources/js/controllers/index.ts');

        $files = $this->getControllerFiles($actionsPath);

        $lines = array_map(function ($file) use ($actionsPath): string {
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', ltrim(str_replace($actionsPath, '', $file), '/'));
            $relativeModule = './actions/'.$relativePath;
            $exportName = pathinfo($file, PATHINFO_FILENAME); // e.g., AboutController

            return sprintf("export { default as %s } from '%s';", $exportName, $relativeModule);
        }, $files);

        $header = "// Auto-generated file – do not edit manually\n\n";
        $content = $header.implode("\n", $lines)."\n";

        File::put($outputPath, $content);

        $this->info('✅ Generated controllers.ts with '.count($files).' exports.');

        return Command::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function getControllerFiles(string $dir): array
    {
        $controllerFiles = [];

        foreach (File::allFiles($dir) as $file) {
            if (str_ends_with($file->getFilename(), 'Controller.ts')) {
                $controllerFiles[] = $file->getPathname();
            }
        }

        return $controllerFiles;
    }
}
