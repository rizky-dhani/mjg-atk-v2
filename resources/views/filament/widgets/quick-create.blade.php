<x-filament-widgets::widget>
    <x-filament::section heading="Quick Create">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach($this->getActions() as $action)
                <a
                    href="{{ $action['url'] }}"
                    class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition cursor-pointer"
                >
                    <x-filament::icon
                        :icon="$action['icon']"
                        :class="'w-8 h-8 text-' . $action['color'] . '-500'"
                    />
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                        {{ $action['label'] }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 text-center">
                        {{ $action['description'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
