<x-filament-widgets::widget>
    <x-filament::section heading="Quick Create">
        <div class="flex flex-wrap justify-center items-center px-4 py-2">
            @foreach($this->getActions() as $action)
                <x-filament::button
                    tag="a"
                    :href="$action['url']"
                    :icon="$action['icon']"
                    color="{{ $action['color'] }}"
                >
                    {{ $action['label'] }}
                </x-filament::button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
