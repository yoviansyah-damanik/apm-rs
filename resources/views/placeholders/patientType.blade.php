<div class="grid grid-cols-2 gap-4">
    @foreach (range(0, 5) as $x)
        <div class="h-32 animate-pulse w-full rounded-lg bg-white grid place-items-center">
            <div class="bg-gray-100 h-8 w-60 rounded-lg"></div>
        </div>
    @endforeach
</div>
