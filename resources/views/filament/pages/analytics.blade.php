<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        {{-- Total Actions Card --}}
        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Total Actions</h3>
                    <p class="text-3xl font-bold text-primary-600">{{ $stats['total_actions'] }}</p>
                </div>
                <div class="rounded-full bg-primary-100 p-3">
                    <x-heroicon-m-chart-bar class="h-6 w-6 text-primary-600" />
                </div>
            </div>
        </x-filament::card>

        {{-- Actions by Type Card --}}
        <x-filament::card>
            <h3 class="text-lg font-medium text-gray-900">Actions by Type</h3>
            <div class="mt-4 space-y-2">
                @foreach($stats['actions_by_type'] as $action => $count)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ ucfirst($action) }}</span>
                        <span class="text-sm font-medium text-gray-900">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </x-filament::card>

        {{-- Recent Activity Card --}}
        <x-filament::card>
            <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
            <div class="mt-4 space-y-2">
                @foreach($stats['recent_activity'] as $activity)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ $activity->description }}</span>
                        <span class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </x-filament::card>

        {{-- User Activity Card --}}
        <x-filament::card>
            <h3 class="text-lg font-medium text-gray-900">User Activity</h3>
            <div class="mt-4 space-y-2">
                @foreach($stats['user_activity'] as $userId => $count)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">User #{{ $userId }}</span>
                        <span class="text-sm font-medium text-gray-900">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
