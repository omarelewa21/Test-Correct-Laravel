<div class="flex flex-col divide-y-2">
    @foreach([1,2,3,4] as $key)
        <div class="p-4 flex space-x-2">
            <div class="icon w-12 bg-allred"></div>
            <div class="content flex flex-col flex-1 relative">
                <span class="bold">title</span>
                <span class="note">description</span>
                <button class="absolute top-0 right-0"><x-icon.plus/></button>
            </div>
        </div>
    @endforeach
</div>