{{-- Injected variables: $event --}}
<div
    class="{{ $styles['event'] }}"
    wire:click.stop="onEventClick('{{ $event['id'] }}')">

    @if($this->hasEventHeader($event))
    <div class="{{ $this->getEventHeaderClass($event) }}">
        {{ $this->getEventHeader($event) }}
    </div>
    @endif

    <div class="flex-1 {{ $this->getEventBodyClass($event) }}"><div>{!! str($this->getEventBody($event))->sanitizeHtml !!}</div></div>

    @if($this->hasEventFooter($event))
    <div class="{{ $this->getEventFooterClass($event) }}">
        {{ $this->getEventFooter($event) }}
    </div>
    @endif

</div>
