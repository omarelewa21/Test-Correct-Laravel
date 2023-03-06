<div id="assessment-page"
     class="min-h-screen w-full"
>
    <x-partials.header.assessment :testName="$testName"/>

    <div style="margin-top: var(--header-height)">
        @dump($this->answers)
    </div>
</div>