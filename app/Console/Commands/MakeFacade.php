<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class MakeFacade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:facade {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Facade and register its Service in the container if needed.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));

        $serviceClass = "{$name}Service";
        $facadeClass = $name;
        $accessor = Str::kebab($name);

        $servicePath = app_path("Services/{$serviceClass}.php");
        $facadePath = app_path("Facades/{$facadeClass}.php");

        File::ensureDirectoryExists(app_path('Services'));
        File::ensureDirectoryExists(app_path('Facades'));

        $this->createServiceIfMissing($servicePath, $serviceClass);
        $this->createFacadeIfMissing($facadePath, $facadeClass, $accessor);

        return self::SUCCESS;
    }

    protected function createServiceIfMissing(string $path, string $class): void
    {
        if (File::exists($path)) {
            $this->line("ℹ Service {$class} already exists, skipping.");

            return;
        }

        $stub = File::get(base_path('stubs/service.stub'));

        $content = str_replace('{{ class }}', $class, $stub);

        File::put($path, $content);
        $this->info("✔ Service {$class} created.");
    }

    protected function createFacadeIfMissing(string $path, string $class, string $accessor): void
    {
        if (File::exists($path)) {
            $this->line("ℹ Facade {$class} already exists, skipping.");

            return;
        }

        $stub = File::get(base_path('stubs/facade.stub'));
        $normalizedAccessor = Str::studly($accessor).'Service';

        $content = str_replace(
            ['{{ class }}', '{{ accessor }}'],
            [$class, $normalizedAccessor],
            $stub,
        );

        File::put($path, $content);
        $this->info("✔ Facade {$class} created.");
    }
}
