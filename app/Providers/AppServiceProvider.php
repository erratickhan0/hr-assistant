<?php

namespace App\Providers;

use App\Services\HrCandidateSearchService;
use App\Services\OpenAIEmbeddingService;
use App\Services\PineconeVectorService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PineconeVectorService::class, fn (): PineconeVectorService => new PineconeVectorService);
        $this->app->singleton(OpenAIEmbeddingService::class, fn (): OpenAIEmbeddingService => new OpenAIEmbeddingService);
        $this->app->singleton(HrCandidateSearchService::class, function ($app): HrCandidateSearchService {
            return new HrCandidateSearchService(
                $app->make(OpenAIEmbeddingService::class),
                $app->make(PineconeVectorService::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
