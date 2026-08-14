<x-filament-widgets::widget>
    <x-filament::section heading="Quick Create">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach($this->getActions() as $action)
                <x-filament::button
                    tag="a"
                    :href="$action['url']"
                    :icon="$action['icon']"
                    color="{{ $action['color'] }}"
                    size="lg"
                    class="justify-center"
                >
                    <div class="flex flex-col items-center gap-1">
                        <span>{{ $action['label'] }}</span>
                        <span class="text-xs opacity-70">{{ $action['description'] }}</span>
                    </div>
                </x-filament::button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
