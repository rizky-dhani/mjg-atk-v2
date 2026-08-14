<x-filament-widgets::widget>
    <x-filament::section heading="Quick Create">
        <div class="flex flex-wrap justify-center items-center px-4 py-2">
            @foreach($this->getActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
