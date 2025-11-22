<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SearchService
{
    /**
     * Search appointments with advanced filters
     */
    public function search(int $userId, array $filters = []): Builder
    {
        $query = Appointment::query()
            ->forUser($userId)
            ->with(['calendar', 'reminders']);

        // Apply search term
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply calendar filter
        if (! empty($filters['calendar_id'])) {
            $query->forCalendar($filters['calendar_id']);
        }

        // Apply status filter
        if (! empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Apply location filter
        if (! empty($filters['location'])) {
            $query->byLocation($filters['location']);
        }

        // Apply color filter
        if (! empty($filters['color'])) {
            $query->byColor($filters['color']);
        }

        // Apply date range filter
        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->betweenDates($filters['start_date'], $filters['end_date']);
        }

        // Apply quick filters
        if (! empty($filters['quick_filter'])) {
            $this->applyQuickFilter($query, $filters['quick_filter']);
        }

        // Apply recurrence filter
        if (isset($filters['is_recurring'])) {
            if ($filters['is_recurring']) {
                $query->recurring();
            } else {
                $query->nonRecurring();
            }
        }

        // Apply all-day filter
        if (isset($filters['is_all_day'])) {
            if ($filters['is_all_day']) {
                $query->allDay();
            } else {
                $query->where('is_all_day', false);
            }
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'start_datetime';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $query->orderBy($sortBy, $sortDirection);

        return $query;
    }

    /**
     * Apply quick filters (today, this week, upcoming, etc.)
     */
    protected function applyQuickFilter(Builder $query, string $filter): void
    {
        match ($filter) {
            'today' => $query->today(),
            'this_week' => $query->thisWeek(),
            'this_month' => $query->thisMonth(),
            'upcoming' => $query->upcoming(),
            'completed' => $query->completed(),
            'cancelled' => $query->cancelled(),
            'scheduled' => $query->scheduled(),
            default => null,
        };
    }

    /**
     * Get saved search queries for a user
     */
    public function getSavedSearches(int $userId): Collection
    {
        // In a real implementation, this would fetch from a saved_searches table
        // For now, return common search templates
        return collect([
            [
                'name' => 'Today\'s Appointments',
                'filters' => ['quick_filter' => 'today'],
            ],
            [
                'name' => 'This Week',
                'filters' => ['quick_filter' => 'this_week'],
            ],
            [
                'name' => 'Upcoming',
                'filters' => ['quick_filter' => 'upcoming'],
            ],
            [
                'name' => 'Business Appointments',
                'filters' => ['calendar_type' => 'business'],
            ],
        ]);
    }

    /**
     * Get search suggestions based on partial input
     */
    public function getSearchSuggestions(int $userId, string $partialSearch, int $limit = 10): Collection
    {
        if (strlen($partialSearch) < 2) {
            return collect();
        }

        // Get unique titles that match
        $titles = Appointment::query()
            ->forUser($userId)
            ->where('title', 'like', "%{$partialSearch}%")
            ->distinct()
            ->limit($limit)
            ->pluck('title');

        // Get unique locations that match
        $locations = Appointment::query()
            ->forUser($userId)
            ->whereNotNull('location')
            ->where('location', 'like', "%{$partialSearch}%")
            ->distinct()
            ->limit($limit)
            ->pluck('location');

        return $titles->merge($locations)->unique()->take($limit);
    }

    /**
     * Get filter statistics for a user (for UI display)
     */
    public function getFilterStatistics(int $userId): array
    {
        $baseQuery = Appointment::query()->forUser($userId);

        return [
            'total' => $baseQuery->count(),
            'today' => (clone $baseQuery)->today()->count(),
            'this_week' => (clone $baseQuery)->thisWeek()->count(),
            'this_month' => (clone $baseQuery)->thisMonth()->count(),
            'upcoming' => (clone $baseQuery)->upcoming()->count(),
            'scheduled' => (clone $baseQuery)->scheduled()->count(),
            'completed' => (clone $baseQuery)->completed()->count(),
            'cancelled' => (clone $baseQuery)->cancelled()->count(),
            'recurring' => (clone $baseQuery)->recurring()->count(),
            'all_day' => (clone $baseQuery)->allDay()->count(),
        ];
    }
}
