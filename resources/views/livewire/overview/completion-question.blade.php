<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="w-full space-y-3" x-data="completionQuestion()"
         x-init="$el.querySelectorAll('input')
                .forEach(function(el){
                    if(el.value == '') {
                        el.classList.add('border-red')
                    }
                 })
             $el.querySelectorAll('select')
                .forEach(function(el){
                    if(el.value == '') {
                        el.classList.add('border-red')
                    }
                 });
                 setTitlesOnLoad($el);
                 ">

        <div>
            <x-input.group class="body1 max-w-full flex-col" for="" x-data="">
                {!! $html !!}
            </x-input.group>
        </div>
    </div>
</x-partials.overview-question-container>
