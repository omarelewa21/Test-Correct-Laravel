<div class="drawer flex z-[20] overflow-auto "
     selid="co-learning-teacher-drawer"
     x-init="
        collapse = window.innerWidth < 1000;
     "
     x-data="{collapse: false}"
>
    <div class="flex flex-col w-full">
        <div class="flex justify-between px-6 pt-3 pb-2 border-b border-bluegrey">
            <div><span class="bold">aanwezig 24</span>/30</div>
            <div><span class="bold">vraag 1</span>/5</div>
        </div>
        <div class="flex flex-col bg-white w-[var(--sidebar-width)] divide-y divide-bluegrey justify-center" >
            @for($i=0;$i<4;$i++)
            {{-- student row --}}
            <div class="flex mx-6 py-2 justify-between">
                {{-- left --}}
                <div>
                    <x-icon.time-dispensation/>
                    <x-icon.close-small/>
                    Froukje Lindemans
                </div>
                {{-- right --}}
                <div>
                    <x-icon.export/>
                </div>
            </div>
                @endfor
        </div>
    </div>
    <!-- Well begun is half done. - Aristotle -->
</div>