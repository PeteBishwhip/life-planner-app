<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PerformanceOptimizationService
{
    /**
     * Cache duration in seconds (1 hour)
     */
    protected int $cacheDuration = 3600;

    /**
     * Get recommended database indexes
     */
    public function getRecommendedIndexes(): array
    {
        return [
            'appointments' => [
                'idx_user_id_start_datetime' => ['user_id', 'start_datetime'],
                'idx_calendar_id_status' => ['calendar_id', 'status'],
                'idx_status_start_datetime' => ['status', 'start_datetime'],
                'idx_recurrence_parent_id' => ['recurrence_parent_id'],
            ],
            'calendars' => [
                'idx_user_id_is_visible' => ['user_id', 'is_visible'],
                'idx_user_id_is_default' => ['user_id', 'is_default'],
            ],
            'appointment_reminders' => [
                'idx_appointment_id_is_sent' => ['appointment_id', 'is_sent'],
                'idx_is_sent' => ['is_sent'],
            ],
        ];
    }

    /**
     * Get query optimization tips
     */
    public function getQueryOptimizationTips(): array
    {
        return [
            'eager_loading' => [
                'tip' => 'Always use eager loading for relationships',
                'example' => 'Appointment::with([\'calendar\', \'user\', \'reminders\'])->get()',
                'benefit' => 'Reduces N+1 query problems',
            ],
            'select_specific_columns' => [
                'tip' => 'Select only needed columns',
                'example' => 'Appointment::select([\'id\', \'title\', \'start_datetime\'])->get()',
                'benefit' => 'Reduces memory usage and transfer time',
            ],
            'chunking' => [
                'tip' => 'Use chunking for large datasets',
                'example' => 'Appointment::chunk(100, function ($appointments) { ... })',
                'benefit' => 'Prevents memory exhaustion',
            ],
            'query_caching' => [
                'tip' => 'Cache frequently accessed queries',
                'example' => 'Cache::remember(\'user-calendars-\'.$userId, 3600, fn() => ...)',
                'benefit' => 'Reduces database load',
            ],
        ];
    }

    /**
     * Clear all application caches
     */
    public function clearAllCaches(): array
    {
        $results = [
            'application_cache' => false,
            'config_cache' => false,
            'route_cache' => false,
            'view_cache' => false,
        ];

        try {
            Cache::flush();
            $results['application_cache'] = true;
        } catch (\Exception $e) {
            \Log::error('Failed to clear application cache: '.$e->getMessage());
        }

        return $results;
    }

    /**
     * Get cache statistics
     */
    public function getCacheStatistics(): array
    {
        return [
            'driver' => config('cache.default'),
            'stores' => array_keys(config('cache.stores')),
        ];
    }

    /**
     * Optimize appointment queries with caching
     */
    public function getOptimizedAppointments(int $userId, array $filters = []): mixed
    {
        $cacheKey = $this->generateCacheKey('appointments', $userId, $filters);

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($userId, $filters) {
            $searchService = new SearchService;

            return $searchService->search($userId, $filters)
                ->select([
                    'id',
                    'calendar_id',
                    'title',
                    'start_datetime',
                    'end_datetime',
                    'is_all_day',
                    'color',
                    'status',
                    'location',
                ])
                ->with(['calendar:id,name,color,type'])
                ->get();
        });
    }

    /**
     * Invalidate cache for user appointments
     */
    public function invalidateUserAppointmentsCache(int $userId): void
    {
        $patterns = [
            "appointments:{$userId}:*",
            "user-calendars-{$userId}",
            "filter-stats-{$userId}",
        ];

        foreach ($patterns as $pattern) {
            // In production, you might use cache tags or a more sophisticated cache invalidation strategy
            Cache::forget($pattern);
        }
    }

    /**
     * Generate cache key
     */
    protected function generateCacheKey(string $prefix, int $userId, array $params = []): string
    {
        $paramsHash = md5(json_encode($params));

        return "{$prefix}:{$userId}:{$paramsHash}";
    }

    /**
     * Get database optimization recommendations
     */
    public function getDatabaseOptimizationRecommendations(): array
    {
        return [
            'indexing' => [
                'status' => 'recommended',
                'description' => 'Add recommended indexes to improve query performance',
                'action' => 'Run migrations to add database indexes',
                'impact' => 'high',
            ],
            'query_optimization' => [
                'status' => 'recommended',
                'description' => 'Use eager loading and select specific columns',
                'action' => 'Review and optimize N+1 queries',
                'impact' => 'high',
            ],
            'caching' => [
                'status' => 'recommended',
                'description' => 'Implement query result caching for frequently accessed data',
                'action' => 'Use Redis or Memcached for caching',
                'impact' => 'medium',
            ],
            'pagination' => [
                'status' => 'recommended',
                'description' => 'Paginate large result sets',
                'action' => 'Implement pagination for appointment lists',
                'impact' => 'medium',
            ],
            'soft_deletes' => [
                'status' => 'optional',
                'description' => 'Consider soft deletes for data recovery',
                'action' => 'Add soft deletes to important models',
                'impact' => 'low',
            ],
        ];
    }

    /**
     * Get frontend optimization recommendations
     */
    public function getFrontendOptimizationRecommendations(): array
    {
        return [
            'lazy_loading' => [
                'status' => 'recommended',
                'description' => 'Lazy load calendar data for past and future dates',
                'action' => 'Implement infinite scroll or pagination',
                'impact' => 'high',
            ],
            'asset_compression' => [
                'status' => 'recommended',
                'description' => 'Compress and minify CSS and JavaScript',
                'action' => 'Configure build tools for production',
                'impact' => 'high',
            ],
            'image_optimization' => [
                'status' => 'recommended',
                'description' => 'Optimize and compress images',
                'action' => 'Use WebP format and responsive images',
                'impact' => 'medium',
            ],
            'caching_headers' => [
                'status' => 'recommended',
                'description' => 'Set appropriate cache headers for static assets',
                'action' => 'Configure server cache headers',
                'impact' => 'medium',
            ],
            'cdn' => [
                'status' => 'optional',
                'description' => 'Use CDN for static assets',
                'action' => 'Set up CloudFront or similar CDN',
                'impact' => 'medium',
            ],
        ];
    }

    /**
     * Run performance diagnostics
     */
    public function runDiagnostics(): array
    {
        return [
            'database' => [
                'connection' => $this->testDatabaseConnection(),
                'query_count' => $this->getQueryCount(),
            ],
            'cache' => [
                'driver' => config('cache.default'),
                'working' => $this->testCache(),
            ],
            'memory' => [
                'current' => $this->formatBytes(memory_get_usage(true)),
                'peak' => $this->formatBytes(memory_get_peak_usage(true)),
            ],
        ];
    }

    /**
     * Test database connection
     */
    protected function testDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get query count (simplified version)
     */
    protected function getQueryCount(): int
    {
        return count(DB::getQueryLog());
    }

    /**
     * Test cache functionality
     */
    protected function testCache(): bool
    {
        try {
            $key = 'performance_test_'.time();
            Cache::put($key, 'test', 60);
            $result = Cache::get($key) === 'test';
            Cache::forget($key);

            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Format bytes to human-readable format
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}
