<div class="drawer flex z-[1]"
     x-data="{collapse: false}"
     x-init="collapse = window.innerWidth < 1000"
     :class="{'collapsed': collapse}"
>
    <div id="sidebar-content" class="flex flex-col">
        <div class="collapse-toggle vertical white z-10 cursor-pointer"
             @click="collapse = !collapse"
        >
            <button class="relative"
                    :class="{'rotate-svg-180 -left-px': !collapse}"
            >
                <x-icon.chevron class="-top-px relative"/>
            </button>
        </div>

        <div id="sidebar-carousel-container"
             x-data="{
                slideWidth: 300,
                init() {
                    this.slideWidth = $root.offsetWidth;
                },
                next(currentEl) {
                    const left = currentEl.scrollLeft + this.slideWidth;
                    this.scroll(left);

                    this.handleVerticalScroll(currentEl.nextElementSibling);
                },
                prev(currentEl) {
                    const left = currentEl.scrollLeft - this.slideWidth;
                    this.scroll(left);
                    this.handleVerticalScroll(currentEl.previousElementSibling);
                },
                home() {
                    this.scroll(0);
                },
                scroll(position) {
                    this.$root.scrollTo({
                        left: position >= 0 ? position : 0,
                        behavior: 'smooth'
                    });
                },
                handleVerticalScroll(el) {
                    const drawer = document.querySelector('.drawer');
                    if (el.offsetHeight > drawer.offsetHeight) {
                        drawer.classList.add('overflow-auto');
                    } else {
                        drawer.classList.remove('overflow-auto');
                    }
                }
             }"
        >
            <x-sidebar.slide-container x-ref="container1" class="p-2.5 border border-allred">
                <x-button.text-button @click="next($refs.container1)">
                    <span>Nieuwe vraag</span>
                    <x-icon.plus/>
                </x-button.text-button>

                @foreach($this->testQuestionUuids as $uuid)
                    <div class="@if($this->testQuestionId === $uuid) border border-primary @endif">
                        <x-button.text-button wire:click="showQuestion('{{ $uuid }}')"
                                              @click="$dispatch('question-change', {old: '{{ $this->testQuestionId }}', new: '{{ $uuid }}' })"
                                              class=""
                        >
                            <span>Vraag {{ $loop->iteration }}</span>
                        </x-button.text-button>
                    </div>
                @endforeach()
            </x-sidebar.slide-container>

            <x-sidebar.slide-container x-ref="container2" class="border border-primary">
                <x-button.text-button class="rotate-svg-180"
                                      @click="prev($refs.container2)">
                    <x-icon.arrow/>
                    <span>Terug</span>
                </x-button.text-button>

                <x-sidebar.question-types/>

            </x-sidebar.slide-container>
        </div>
    </div>
</div>