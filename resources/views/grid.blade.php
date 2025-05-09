<div class="@if($dragToScroll) drag-to-scroll @endif @if($dragToCreate) drag-to-create @endif">
    @if($dragToScroll)
    <div class="z-10 fixed p-2 bg-white text-xs border border-black rounded-lg rounded-tl-none shadow-md drag-to-scroll-pointer-note" style="display: none;" ></div>
    @endif
    @if($dragToCreate)
    <div class="z-10 fixed border border-black bg-black drag-to-create-pointer-note pointer-none" style="display: none;" ></div>
    @endif
    <div>
        @includeIf($beforeGridView)
    </div>

    <div class="flex" wire:mouseleave="onGridMouseLeave">

        @include($hoursColumnView, ['hoursAndSlots' => $hoursAndSlots])

        <div class="overflow-x-auto w-full">
            <div class="inline-block min-w-full overflow-hidden">
                <div class="grid grid-flow-col">
                    @foreach($resources as $resource)
                        @include($resourceColumnView, [
                            'hoursAndSlots' => $hoursAndSlots,
                            'resource' => $resource,
                            'interval' => $interval,
                        ])
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div>
        @includeIf($afterGridView)
</div>



