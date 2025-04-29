<?php

namespace Team383\LivewireResourceTimeGrid;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Reactive;
use Livewire\Component;

/**
 * Class LivewireResourceTimeGrid
 * @package Team383\LivewireResourceTimeGrid
 * @property string $gridView
 * @property string $hoursColumnView
 * @property string $hourView
 * @property string $resourceColumnView
 * @property string $resourceColumnHeaderView
 * @property string $resourceColumnHourSlotView
 * @property string $eventView
 * @property int $hourHeightInRems
 * @property int $resourceColumnHeaderHeightInRems
 */
class LivewireResourceTimeGrid extends Component
{
    #[Reactive]
    public $startingHour;
    #[Reactive]
    public $endingHour;
    #[Reactive]
    public $interval;

    public $gridView;
    public $hoursColumnView;
    public $hourView;
    public $resourceColumnView;
    public $resourceColumnHeaderView;
    public $resourceColumnHourSlotView;
    public $eventView;

    #[Reactive]
    public $hourHeightInRems;
    public $resourceColumnHeaderHeightInRems;

    public $beforeGridView;
    public $afterGridView;

    public $model;

    public $dragToScroll;
    public $dragToCreate;

    public $gutter;

    protected $listeners = [
        'hourSlotClick' => 'hourSlotClick',
        'onEventClick' => 'onEventClick',
        'onEventDropped' => 'onEventDropped',
        'onRefreshResourceTimeGrid' => '$refresh',
    ];

    public function mount(
        $startingHour,
        $endingHour,
        $interval,
        $gridView = null,
        $hoursColumnView = null,
        $hourView = null,
        $resourceColumnView = null,
        $resourceColumnHeaderView = null,
        $resourceColumnHourSlotView = null,
        $eventView = null,
        $beforeGridView = null,
        $afterGridView = null,
        $resourceColumnHeaderHeightInRems = 4,
        $hourHeightInRems = 8,
        $extras = null,
        $model = null,
        $dragToScroll = false,
        $dragToCreate = false,
        $gutter = 10,
    ) {
        $this->startingHour = $startingHour;
        $this->endingHour = $endingHour;
        $this->interval = $interval;

        $this->gridView = $gridView ?? 'livewire-resource-time-grid::grid';
        $this->hoursColumnView = $hoursColumnView ?? 'livewire-resource-time-grid::hours-column';
        $this->hourView = $hourView ?? 'livewire-resource-time-grid::hour';
        $this->resourceColumnView = $resourceColumnView ?? 'livewire-resource-time-grid::resource-column';
        $this->resourceColumnHeaderView = $resourceColumnHeaderView ?? 'livewire-resource-time-grid::resource-column-header';
        $this->resourceColumnHourSlotView = $resourceColumnHourSlotView ?? 'livewire-resource-time-grid::resource-column-hour-slot';
        $this->eventView = $eventView ?? 'livewire-resource-time-grid::event';

        $this->beforeGridView = $beforeGridView;
        $this->afterGridView = $afterGridView;

        $this->hourHeightInRems = $hourHeightInRems;
        $this->resourceColumnHeaderHeightInRems = $resourceColumnHeaderHeightInRems;

        $this->model = $model;

        $this->dragToScroll = $dragToScroll;
        $this->dragToCreate = $dragToCreate;

        $this->gutter = $gutter;

        $this->afterMount($extras);
    }

    public function afterMount($extras)
    {
        $this->dispatch('onLivewireResourceTimeGridMounted', $extras);
    }

    public function rendered()
    {
        $this->dispatch('onLivewireResourceTimeGridMounted');
    }

    public function resources()
    {
        return collect();
    }

    public function events()
    {
        return collect();
    }

    public function isEventForResource($event, $resource)
    {
        return $event['resource_id'] == $resource['id'];
    }

    public function hourSlotClick($resourceId, $hour, $slot)
    {
        //
    }

    public function onEventClick($eventId)
    {
        //
    }

    public function onEventDropped($eventId, $resourceId, $hour, $slot)
    {
        //
    }

    public function onGridMouseLeave()
    {
        //
    }

    public function styles()
    {
        return [
            'intersect' => 'border',

            'hourAndSlotsContainer' => 'border relative -mt-px bg-gray-100',

            'hourWrapper' => 'border relative -mt-px bg-white',

            'hour' => 'p-2 text-xs text-gray-600 flex justify-center items-center',

            'resourceColumnHeader' => 'h-full text-xs flex justify-center items-center',

            'resourceColumnHourSlot' => 'border-b hover:bg-blue-100 cursor-pointer',

            'eventWrapper' => 'absolute top-0 left-0',

            'event' => 'rounded h-full flex flex-col overflow-hidden w-full shadow-lg border',

            'eventTitle' => 'text-xs font-medium bg-indigo-500 p-2 text-white',

            'eventBody' => 'text-xs bg-white flex-1 p-2',
        ];
    }

    public function render()
    {
        $events = $this->events();

        $resources = $this->resources()
            ->map(function ($resource) use ($events) {
                $resource['events'] = $this->getEventsForResource($resource, $events);
                return $resource;
            });

        return view($this->gridView)
            ->with('hoursAndSlots', $this->hoursAndSlots())
            ->with('resources', $resources)
            ->with('styles', $this->styles())
            ->with('getEventsInHourSlot', function ($hour, $slot, $events) {
                return $this->getEventsInHourSlot($hour, $slot, $events);
            })
            ->with('getEventStyles', function ($event, $events) {
                return $this->getEventStyles($event, $events);
            })
            ;
    }

    private function hoursAndSlots()
    {
        return collect(range($this->startingHour, $this->endingHour))
            ->map(function ($hour) {
                return [
                    'hour' => $hour,
                    'slots' => range(0, 60 - $this->interval, $this->interval)
                ];
            });
    }

    private function getEventsForResource($resource, Collection $events) : Collection
    {
        return $events
            ->filter(function ($event) use ($resource) {
                return $this->isEventForResource($event, $resource);
            })
            ->map(function ($event) use ($resource) {
                $event['resource'] = $resource;
                $event['start_ts'] = $event['starts_at']->timestamp;
                $event['end_ts'] = $event['ends_at']->timestamp;
                return $event;
            })
            ->sortBy('start_ts')
            ->values()
            ->pipe(function ($events) {
                $columns = collect(); // Each item is a collection of events in that column

                $eventsWithColumns = $events->map(function ($event) use (&$columns) {
                    // Find first column where it doesn’t overlap the last event
                    $colIndex = $columns->search(function ($col) use ($event) {
                        $last = $col->last();
                        return $last['end_ts'] <= $event['start_ts'];
                    });

                    if ($colIndex === false) {
                        $colIndex = $columns->count();
                        $columns->push(collect([$event]));
                    } else {
                        $columns[$colIndex]->push($event);
                    }

                    $event['col'] = $colIndex;
                    return $event;
                });

                return $eventsWithColumns->map(function ($event) use ($eventsWithColumns) {
                    // Find overlapping events
                    $overlappingCols = $eventsWithColumns
                        ->filter(function ($other) use ($event) {
                            return $event !== $other &&
                                $event['start_ts'] < $other['end_ts'] &&
                                $event['end_ts'] > $other['start_ts'];
                        })
                        ->pluck('col')
                        ->push($event['col'])
                        ->unique()
                        ->sort()
                        ->values();

                    $totalCols = $overlappingCols->max() + 1;
                    $width = round((100 - $this->gutter) / $totalCols, 2);
                    $left = round($event['col'] * $width, 2);

                    return collect($event)->merge([
                        'width' => $width,
                        'left' => $left,
                    ]);
                });
            });
    }

    private function getEventsInHourSlot($hour, $slot, Collection $events) : Collection
    {
        return $events
            ->filter(function ($event) use ($hour, $slot) {
                /** @var Carbon $eventStartsAt */
                $eventStartsAt = $event['starts_at'];

                /** @var Carbon $hourSlotStartsAt */
                $hourSlotStartsAt = $eventStartsAt->clone()
                    ->setTime($hour, $slot);

                /** @var Carbon $hourSlotEndsAt */
                $hourSlotEndsAt = $eventStartsAt->clone()
                    ->setTime($hour, $slot)
                    ->addMinutes($this->interval);

                return $eventStartsAt->timestamp >= $hourSlotStartsAt->timestamp
                    && $eventStartsAt->timestamp < $hourSlotEndsAt->timestamp
                    ;
            });
    }

    private function eventHourSlotFraction($event)
    {
        return $event['starts_at']->minute / $this->interval;
    }

    public function hourSlotIntervalHeightInRems()
    {
        return $this->hourHeightInRems / (60 / $this->interval);
    }

    private function getEventStyles($event, $events)
    {
        $marginTop = $this->eventHourSlotFraction($event) * $this->hourSlotIntervalHeightInRems();
        $height = $event['starts_at']->diffInMinutes($event['ends_at']) / $this->interval * $this->hourSlotIntervalHeightInRems();
        $height -= 0.05; // Magic fix 😅 (This amount adds some space between the ending edge of the event and the next one below)
        $width = $event["width"];
        $marginLeft = $event["left"];


        return collect([
            "margin-left: {$marginLeft}%",
            "margin-top: {$marginTop}rem",
            "max-height: {$height}rem",
            "height: {$height}rem",
            "min-height: {$height}rem",
            "width: {$width}%",
            "z-index: 1",
            "overflow: hidden",
        ])->implode('; ');
    }

    public function getEventHeader($event)
    {
        if (isset($event['header'])) {
            return $event['header'];
        }
        return $event['starts_at']->format('h:i A') . ' - ' . $event['ends_at']->format('h:i A');
    }

    public function getEventHeaderClass($event)
    {
        if (isset($event['header_class'])) {
            return $event['header_class'];
        }
        return $this->styles()['eventTitle'];
    }

    public function hasEventHeader($event)
    {
        return !empty($this->getEventHeader($event));
    }

    public function getEventBody($event)
    {
        return $event['body'];
    }

    public function getEventBodyClass($event)
    {
        if (isset($event['body_class'])) {
            return $event['body_class'];
        }
        return $this->styles()['eventBody'];
    }

    public function getEventFooter($event)
    {
        if (isset($event['footer'])) {
            return $event['footer'];
        }
        return $event['starts_at']->format('h:i A') . ' - ' . $event['ends_at']->format('h:i A');
    }

    public function getEventFooterClass($event)
    {
        if (isset($event['footer_class'])) {
            return $event['footer_class'];
        }
        return $this->styles()['eventTitle'];
    }

    public function hasEventFooter($event)
    {
        return !empty($this->getEventFooter($event));
    }

}
