<div class="flex flex-col divide-y-2">
    <span></span>

    <span class="note text-sm uppercase text-center">Open vragen</span>
    @foreach($this->newQuestions['open'] as $question)
        <div class="p-4 flex space-x-2 items-center text-sm">
            <div class="">
                <x-stickers.question-arq/>
            </div>
            <div class="content flex flex-col flex-1 relative">
                <span class="bold">{{ $question['name'] }}</span>
                <span class="note">{{ $question['description'] }}</span>
                <button class="absolute top-0 right-0"><x-icon.plus/></button>
            </div>
        </div>
    @endforeach

    <span class="note text-sm uppercase text-center">Open vragen</span>
    @foreach($this->newQuestions['closed'] as $question)
        <div class="p-4 flex space-x-2">
            <div class="icon w-12 bg-allred"></div>
            <div class="content flex flex-col flex-1 relative">
                <span class="bold">title</span>
                <span class="note">description</span>
                <button class="absolute top-0 right-0"><x-icon.plus/></button>
            </div>
        </div>
    @endforeach

    <span class="note text-sm uppercase text-center">Open vragen</span>
    @foreach($this->newQuestions['extra'] as $question)
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