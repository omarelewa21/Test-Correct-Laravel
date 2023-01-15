<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
            <div class="flex justify-between flex-1 sm:hidden">
                <span>
                    @if ($paginator->onFirstPage())
                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                            {!! __('pagination.previous') !!}
                        </span>
                    @else
                        <button wire:click="previousPage" wire:loading.attr="disabled" dusk="previousPage.before" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                            {!! __('pagination.previous') !!}
                        </button>
                    @endif
                </span>

                <span>
                    @if ($paginator->hasMorePages())
                        <button wire:click="nextPage" wire:loading.attr="disabled" dusk="nextPage.before" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                            {!! __('pagination.next') !!}
                        </button>
                    @else
                        <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                            {!! __('pagination.next') !!}
                        </span>
                    @endif
                </span>
            </div>

            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div class="hidden">
                    <p class="text-sm text-gray-700 leading-5">
                        <span>{!! __('Showing') !!}</span>
                        <span class="font-medium">{{ $paginator->firstItem() }}</span>
                        <span>{!! __('to') !!}</span>
                        <span class="font-medium">{{ $paginator->lastItem() }}</span>
                        <span>{!! __('of') !!}</span>
                        <span class="font-medium">{{ $paginator->total() }}</span>
                        <span>{!! __('results') !!}</span>
                    </p>
                </div>

                <div class="justify-center flex w-full" x-data>
                    <span class="relative z-0 inline-flex items-center">
                        <span class="mr-4">
                            {{-- Previous Page Link --}}
                            @if ($paginator->onFirstPage())
                                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                                    <x-button.text-button class="text-midgrey px-2" aria-disabled="true" disabled aria-label="{{ __('pagination.previous') }}">
                                        <x-icon.arrow-left/>
                                    </x-button.text-button>
                                </span>

                                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                                    <x-button.text-button class="text-midgrey rotate-svg-180" disabled aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                                        <x-icon.chevron/>
                                        <span>{{ __('pagination.previous') }}</span>
                                    </x-button.text-button>
                                </span>
                            @else
                                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                                    <x-button.text-button class="px-2" @click="$wire.gotoPage(1)" aria-label="{{ __('pagination.previous') }}">
                                        <x-icon.arrow-left/>

                                    </x-button.text-button>
                                </span>
                                <x-button.text-button wire:click="previousPage" dusk="previousPage.after" rel="prev" aria-label="{{ __('pagination.previous') }}" class="rotate-svg-180">
                                    <x-icon.chevron/>
                                    <span>{{ __('pagination.previous') }}</span>
                                </x-button.text-button>
                            @endif
                        </span>
                        {{-- Pagination Elements --}}
                        @foreach ($elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <span aria-disabled="true">
                                    <span class="relative px-2">{{ $element }}</span>
                                </span>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                <div class="inline-flex space-x-2">
                                @foreach ($element as $page => $url)
                                    <span wire:key="paginator-page{{ $page }}">
                                        @if ($page == $paginator->currentPage())
                                            <span aria-current="page">
                                                <span class="relative cursor-default inline-flex items-center justify-center w-8 h-8 transition ease-in-out duration-150 bg-primary text-sm text-white rounded-full focus:outline-none">
                                                    {{ $page }}
                                                </span>
                                            </span>
                                        @else
                                            <button wire:click="gotoPage({{ $page }})" class="relative paginator-button inline-flex items-center justify-center w-8 h-8 transition ease-in-out duration-150  text-sm base border-3 border-system-base rounded-full focus:outline-none" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                            {{ $page }}
                                            </button>
                                        @endif
                                    </span>
                                @endforeach
                                </div>
                            @endif
                        @endforeach
                        <span class="ml-4">
                            {{-- Next Page Link --}}
                            @if ($paginator->hasMorePages())
                                <x-button.text-button wire:click="nextPage" dusk="nextPage.after" rel="next" aria-label="{{ __('pagination.next') }}">
                                    <span>{{ __('pagination.next') }}</span>
                                    <x-icon.chevron/>
                                </x-button.text-button>
                                <x-button.text-button class="px-2"  wire:click="gotoPage({{ ceil($paginator->total() / $paginator->perPage()) }})" aria-label="{{ __('pagination.next') }}">
                                    <x-icon.arrow/>
                                </x-button.text-button>
                            @else
                                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                                    <x-button.text-button class="text-midgrey" disabled>
                                        <span>{{ __('pagination.next') }}</span>
                                        <x-icon.chevron/>
                                    </x-button.text-button>
                                </span>
                                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                                    <x-button.text-button class="px-2 text-midgrey" disabled>
                                         <x-icon.arrow/>
                                    </x-button.text-button>
                                </span>
                            @endif
                        </span>
                    </span>
                </div>
            </div>
        </nav>
    @endif
</div>
