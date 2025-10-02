import { router } from '@inertiajs/vue3';
import { computed, reactive, readonly } from 'vue';

export interface TableFilters {
    [key: string]: string | number | boolean | null;
}

interface UseTableFiltersOptions {
    routeName: string;
    initialFilters?: TableFilters;
    debounceMs?: number;
    excludeFromUrl?: string[];
}

export function useTableFilters(options: UseTableFiltersOptions) {
    const { routeName, initialFilters = {}, debounceMs = 300, excludeFromUrl = [] } = options;

    const filters = reactive<TableFilters>({ ...initialFilters });

    const hasActiveFilters = computed(() => {
        return Object.entries(filters).some(([key, value]) => {
            if (key === 'sort' || key === 'direction') return false;
            return value !== '' && value !== null && value !== undefined;
        });
    });

    let debounceTimeout: ReturnType<typeof setTimeout>;

    function debounce(callback: () => void) {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(callback, debounceMs);
    }

    function applyFilters(resetPage = true) {
        const params = buildParams(resetPage ? 1 : undefined);
        navigate(params);
    }

    function updateFilter(key: string, value: any, shouldDebounce = false) {
        filters[key] = value;

        if (shouldDebounce) {
            debounce(() => applyFilters());
        } else {
            applyFilters();
        }
    }

    function sortBy(column: string) {
        if (filters.sort === column) {
            filters.direction = filters.direction === 'asc' ? 'desc' : 'asc';
        } else {
            filters.sort = column;
            filters.direction = 'asc';
        }
        applyFilters(false);
    }

    function clearFilters() {
        Object.keys(filters).forEach((key) => {
            if (key === 'sort' || key === 'direction') {
                filters[key] = initialFilters[key] || '';
            } else {
                filters[key] = '';
            }
        });

        router.get(
            route(routeName),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    }

    function goToPage(page: number) {
        const params = buildParams(page);
        navigate(params);
    }

    function buildParams(page?: number): Record<string, any> {
        const params: Record<string, any> = {};

        Object.entries(filters).forEach(([key, value]) => {
            if (!excludeFromUrl.includes(key) && value !== '' && value !== null && value !== undefined) {
                params[key] = value;
            }
        });

        if (page !== undefined) {
            params.page = page;
        }

        return params;
    }

    function navigate(params: Record<string, any>) {
        router.get(route(routeName), params, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    }

    // Return filters as readonly refs to prevent accidental mutation
    return {
        filters: readonly(filters), // Make it readonly to force using updateFilter
        hasActiveFilters,
        updateFilter,
        applyFilters,
        sortBy,
        clearFilters,
        goToPage,
    };
}
