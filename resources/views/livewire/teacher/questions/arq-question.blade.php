@extends('livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms"
    />
@endsection

@section('question-cms-answer')


    <div class="flex flex-col space-y-2 w-full mt-4"
    >
        <div class="flex px-0 py-0 border-0 bg-system-white justify-between relative">
            <table class="min-w-full devide-y devide-gray-200">
                <thead class="border-b-gray-200">
                    <tr>
                        <th></th>
                        <th class="whitespace-nowrap px-4 border-b-1">St. 1</th>
                        <th class="whitespace-nowrap px-4">St. 2</th>
                        <th class="text-left w-full px-4">{{ __('Reden') }}</th>
                        <th  class="text-center">{{ __('Score') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>A</td>
                        <td class="px-4 text-center">{{ __('J') }}</td>
                        <td class="px-4 text-center">{{ __('J') }}</td>
                        <td class="px-4">{{ __('Juiste reden') }}</td>
                        <td class="text-center">
                            <x-input.text class="w-12 text-center"
                                      wire:model.lazy="cmsPropertyBag.answerStruct.0.score"
                                      title="{{ $this->cmsPropertyBag['answerStruct'][0]['score'] }}"
                                      type="number"
                            />
                        </td>
                    </tr>
                    <tr>
                    <!-- row B -->
                        <td>B</td>
                        <td class="px-4 text-center">{{ __('J') }}</td>
                        <td class="px-4 text-center">{{ __('J') }}</td>
                        <td class="px-4">{{ __('Onjuiste reden') }}</td>
                        <td class="text-center">
                            <x-input.text class="w-12 text-center"
                                          wire:model.lazy="cmsPropertyBag.answerStruct.1.score"
                                          title="{{ $this->cmsPropertyBag['answerStruct'][1]['score'] }}"
                                          type="number"
                            />
                        </td>
                    </tr>

                    <tr>
                        <td>C</td>
                        <td class="px-4 text-center">{{ __('J') }}</td>
                        <td class="px-4 text-center">{{ __('O') }}</td>
                        <td class="px-4">-</td>
                        <td class="text-center">
                            <x-input.text class="w-12 text-center"
                                          wire:model.lazy="cmsPropertyBag.answerStruct.2.score"
                                          title="{{ $this->cmsPropertyBag['answerStruct'][2]['score'] }}"
                                          type="number"
                            />
                        </td>
                    </tr>

                    <tr>
                        <td>D</td>
                        <td class="px-4 text-center">{{ __('O') }}</td>
                        <td class="px-4 text-center">{{ __('J') }}</td>
                        <td class="px-4">-</td>
                        <td class="text-center">
                            <x-input.text class="w-12 text-center"
                                          wire:model.lazy="cmsPropertyBag.answerStruct.3.score"
                                          title="{{ $this->cmsPropertyBag['answerStruct'][3]['score'] }}"
                                          type="number"
                            />
                        </td>
                    </tr>

                    <tr>
                        <td>E</td>
                        <td class="px-4 text-center">{{ __('O') }}</td>
                        <td class="px-4 text-center">{{ __('O') }}</td>
                        <td class="px-4">-</td>
                        <td class="text-center">
                            <x-input.text class="w-12 text-center"
                                          wire:model.lazy="cmsPropertyBag.answerStruct.4.score"
                                          title="{{ $this->cmsPropertyBag['answerStruct'][4]['score'] }}"
                                          type="number"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @error('question.answers.*.*')
    <div class="notification error stretched mt-4">
        <span class="title">{{ __('cms.De gemarkeerde velden zijn verplicht') }}</span>
    </div>
    @enderror

    @error('question.score')
    <div class="notification error stretched mt-4">
        <span class="title">{{ __('cms.Er dient minimaal 1 punt toegekend te worden') }}</span>
    </div>
    @enderror

@endsection
