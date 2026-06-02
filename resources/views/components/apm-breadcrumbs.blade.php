@props([
    'menus' => [],
    'currentStep' => 1,
])

<flux:breadcrumbs class="px-4 py-2 my-4 bg-white rounded-lg">
    @if (Request::routeIs('home'))
        <flux:breadcrumbs.item separator="slash">
            <div class="!flex items-center gap-1">
                <flux:icon.home variant="solid" />
                <div class="flex-1">
                    Halaman Awal
                </div>
            </div>
        </flux:breadcrumbs.item>
    @else
        <flux:breadcrumbs.item :href="route('home')" separator="slash">
            <div class="!flex items-center gap-1">
                <flux:icon.home variant="solid" />
                <div class="flex-1">
                    Halaman Awal
                </div>
            </div>
        </flux:breadcrumbs.item>
    @endif
    @foreach ($menus as $menu)
        @if ($menu['step'] < $currentStep)
            <flux:breadcrumbs.item separator="slash" wire:click="$set('currentStep',{{ $menu['step'] }})">
                <div class="!text-black cursor-pointer">
                    {{ $menu['title'] }}
                </div>
            </flux:breadcrumbs.item>
        @else
            <flux:breadcrumbs.item separator="slash">
                <div @class([
                    $currentStep == $menu['step'] ? '!text-primary-700' : '',
                    $currentStep > $menu['step'] ? '!text-black' : '',
                ])>
                    {{ $menu['title'] }}
                </div>
            </flux:breadcrumbs.item>
        @endif
    @endforeach
</flux:breadcrumbs>
