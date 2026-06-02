<div class="mx-auto">
    <div class="grid gap-4">
        @foreach (range(1, 8) as $x)
            <div
                class="h-32 animate-pulse w-full rounded-lg bg-white space-y-2 flex flex-col items-center justify-center px-4">
                <div class="bg-gray-100 h-8 w-60 rounded-lg"></div>
                <div class="bg-gray-100 h-5 w-full rounded-lg"></div>
                <div class="bg-gray-100 h-3 w-48 rounded-lg"></div>
            </div>
        @endforeach
    </div>
</div>
