{{-- Injected variables: $resource, $eventsInHourSlot --}}
<div
    class="{{ $styles['resourceColumnHourSlot'] }}"
    style="height: {{ $hourHeightInRems / (60/$interval) }}rem;"
    id="{{ $_instance->id }}-{{ $resource['id'] }}-{{ $hour }}-{{$slot}}"

    ondragenter="onLivewireResourceTimeGridEventDragEnter(event, '{{ $_instance->id }}', '{{ $resource['id'] }}', {{ $hour }}, {{ $slot }});"
    ondragleave="onLivewireResourceTimeGridEventDragLeave(event, '{{ $_instance->id }}', '{{ $resource['id'] }}', {{ $hour }}, {{ $slot }});"
    ondragover="onLivewireResourceTimeGridEventDragOver(event);"
    ondrop="onLivewireResourceTimeGridEventDrop(event, '{{ $_instance->id }}', '{{ $resource['id'] }}', {{ $hour }}, {{ $slot }});"

    wire:click.stop="hourSlotClick('{{ $resource['id'] }}', {{ $hour }}, {{ $slot }})"
    @if($dragToScroll || $dragToCreate)
    data-resource-id="{{ $resource['id'] }}"
    data-resource-title="{{ $resource['title'] }}"
    data-hour="{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}"
    data-slot="{{ str_pad($slot, 2, '0', STR_PAD_LEFT) }}"
    @endif
>
    @foreach($eventsInHourSlot as $event)
        <div
            class="{{ $styles['eventWrapper'] }}"
            style="{{ $getEventStyles($event, $events) }}"

            draggable="true"
            ondragstart="onLivewireResourceTimeGridEventDragStart(event, '{{ $event['id'] }}')"

            wire:click.stop=""
        >
            @include($eventView, [
                'event' => $event,
            ])
        </div>
    @endforeach

</div>
