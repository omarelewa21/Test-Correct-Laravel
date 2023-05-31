<div>
    <div style="padding: 20px; padding-top: 150px; height: 100vh"
         wire:ignore
         x-data="CkEditorComments(
            @js($users),
            '1486',
            'answer-editor',
            'comment-editor',
            @js($this->commentThreads)
         )"


    >
        <style type="text/css">


            /* hides the annotiation */
            .ck .ck.ck-balloon-panel {
                display: none;
            }


            .ck-content .ck-comment-marker {
                background: rgba(0, 77, 245, 0.2);
            }
            .ck-content .ck-comment-marker--active {
                background: rgba(0, 77, 245, 0.7);
            }

            .ck.ck-editor__main {
                padding-top: 76px !important;
            }
            .ck.ck-content {
                min-height: 150px !important;
            }
        </style>


        <div id="container" class="flex justify-between h-full relative">
            <div class="flex px-15 flex-col">
                <textarea id="editor">{{ $answer }}</textarea>

                <div class="w-full p-12 whitespace-pre-line" >
                    2 permanent editors + one editor per comment thread:
                     * 'answer-editor'
                     * 'comment-editor'
                     * 'thread-1'... etc.
                </div>
            </div>
            <div id="sidebar" class="flex flex-col h-full min-w-[var(--sidebar-width)] max-w-[var(--sidebar-width)] bg-white">
                <div class="flex w-full justify-center gap-2 z-1" style="box-shadow: 0 3px 8px 0 rgba(4, 31, 116, 0.2);">
                    <span class="h-[60px] flex justify-center items-center gap-2 bold">
                        <x-icon.feedback-text/>
                        <span>Feedback geven</span>
                    </span>
                </div>
                <div class="w-full flex flex-col p-6 gap-4">

                    <div class="w-full">
                        <div class="w-full">
                            markeren
                        </div>
                        <div class="w-full flex justify-between gap-2">
                            <x-icon.warning/>
                            <x-button.colored-circle color="1"></x-button.colored-circle>
                            <x-button.colored-circle color="2"></x-button.colored-circle>
                            <x-button.colored-circle color="3"></x-button.colored-circle>
                            <x-button.colored-circle color="4"></x-button.colored-circle>
                            <x-button.colored-circle color="5"></x-button.colored-circle>
                            <x-button.colored-circle color="6"></x-button.colored-circle>
                            <x-button.colored-circle color="7"></x-button.colored-circle>
                        </div>
                    </div>
                    <div class="w-full">
                        <div class="w-full">
                            Emoijen
                        </div>
                        <div class="w-full flex justify-between gap-2">
                            <x-button.colored-circle color="cta">v</x-button.colored-circle>
                            <x-button.colored-circle color="all-red">x</x-button.colored-circle>
                            <x-button.colored-circle color="teacher-primary-light">?</x-button.colored-circle>
                            {{-- feest --}}
                            {{-- thumbs up --}}
                            {{-- thumbs down= --}}
                            <x-button.colored-circle color="cta"><x-icon.smiley-happy/> </x-button.colored-circle>
                            <x-button.colored-circle color="orange"><x-icon.smiley-normal/> </x-button.colored-circle>
                            <x-button.colored-circle color="red"><x-icon.smiley-sad/></x-button.colored-circle>
                        </div>
                    </div>

                    <x-input.rich-textarea type="assessment-feedback" editor-id="comment-editor"> </x-input.rich-textarea>
                    <div class="flex flex-row-reverse justify-start gap-2">
                        <x-button.cta @click="saveCommentThread">
                            <span>opslaan</span>
                        </x-button.cta>
                    </div>

                </div>
                <div class="flex w-full justify-center gap-2 z-1" style="box-shadow: 0 3px 8px 0 rgba(4, 31, 116, 0.2);">
                    <span class="h-[60px] flex justify-center items-center gap-2 bold">
                        <x-icon.feedback-text/>
                        <span>Feedback aanpassen</span>
                    </span>
                </div>
                @foreach($this->commentThreads as $key => $commentThread)
                    {{$commentThread['threadId']}}
                        {{--$commentThread['comments'][0]['commentId']--}}

                    <div class="w-full flex flex-col p-6 gap-4" editor-container="{{$commentThread['threadId']}}"
                         x-data="{threadId:  @js($commentThread['threadId'])}"
                         x-init="
                    $nextTick(()=> {
                        temp = $el.querySelector('.ck.ck-content');
console.log(temp);
                        temp.addEventListener('blur', ()=>{
                            this.commentsRepository.setActiveCommentThread(null);
                        })
                        temp.addEventListener('focus', ()=>{
                            console.log('focus')
                            console.log(threadId)
                            setTimeout(() => {
                                                        this.commentsRepository.setActiveCommentThread(threadId);
                            },50)
                        })

                    })

                    ">
                        <x-input.rich-textarea type="assessment-feedback" editor-id="{{$commentThread['threadId']}}"> </x-input.rich-textarea>
                    </div>
                    <div class="flex flex-row-reverse justify-start gap-2">
                        <x-button.cta @click="updateCommentThread('{{$commentThread['threadId']}}')">
                            <span>opslaan</span>
                        </x-button.cta>
                        <x-button.secondary @click="deleteCommentThread('{{$commentThread['threadId']}}')">
                            <span>delete</span>
                        </x-button.secondary>
                    </div>
                @endforeach
            </div>
        </div>

        <button id="get-data">Get editor data</button>

    </div>

</div>
