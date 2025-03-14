# Livewire Resource Time Grid

This package allows you to build resource/time grid to show events in a "calendar" way. You can define resources as 
anything that owns an event, eg. a particular day, a user, a client, etc. Events loaded with the component will be then
rendered in columns according to the resource it belongs to and the starting date of the event. 

This package is based on https://github.com/asantibanez/livewire-resource-time-grid, but has significantly diverged from 
this in order to support Laravel 11 and Livewire 3, and also provides various improvements and additional features.

## Preview

![preview](https://github.com/383Project/livewire-resource-time-grid/raw/master/preview.gif)

## Installation

You can install the package via composer:

```bash
composer require team383/livewire-resource-time-grid
```

## Requirements

This package uses `livewire/livewire` (https://laravel-livewire.com/) under the hood.

It also uses TailwindCSS (https://tailwindcss.com/) for base styling. 

Please make sure you include both of this dependencies before using this component. 

## Usage

In order to use this component, you must create a new Livewire component that extends from 
`LivewireResourceTimeGrid`

You can use `make:livewire` to create a new component. For example.
``` bash
php artisan make:livewire AppointmentsGrid
```

In the `AppointmentsGrid` class, instead of extending from the base `Component` Livewire class, 
extend from `LivewireResourceTimeGrid`. Also, remove the `render` method. 
You'll have a class similar to this snippet.
 
``` php
class AppointmentsGrid extends LivewireResourceTimeGrid
{
    //
}
```

In this class, you must override the following methods

```php
public function resources()
{
    // must return a Laravel collection
}

public function events()
{
    // must return a Laravel collection
}
```

In `resources()` method, return a collection holding the "resources" that own the events
that are going to be listed in the grid. These "resources" must be arrays with `key => value` pairs
and must include an `id` and a `title`. You can add any other keys to each "resource as needed"

Example

```php
public function resources()
{
    return collect([
        ['id' => 'andres', 'title' => 'Andres'],
        ['id' => 'pamela', 'title' => 'Pamela'],
        ['id' => 'sara', 'title' => 'Sara'],
        ['id' => 'bruno', 'title' => 'Bruno'],
    ]);
}
```

In the `events()` method, return a collection holding the events that belong to each of the "resources"
returned in the `resources()` method. Events must also be keyed arrays holding at least the following keys: 
`id`, `title`, `starts_at`, `ends_at`, `resource_id`. 

Also, the following conditions are expected for each returned event: 
- For each event `resource_id` must match an `id` in the `resources()` returned collection.
- `starts_at` must be a `Carbon\Carbon` instance
- `ends_at` must be a `Carbon\Carbon` instance

Example

```php
public function events()
{
    return collect([
        [
            'id' => 1,
            'title' => 'Breakfast',
            'starts_at' => Carbon::today()->setTime(10, 0),
            'ends_at' => Carbon::today()->setTime(12, 0),
            'resource_id' => 'andres',
        ],
        [
            'id' => 2,
            'title' => 'Lunch',
            'starts_at' => Carbon::today()->setTime(13, 0),
            'ends_at' => Carbon::today()->setTime(15, 0),
            'resource_id' => 'pamela',
        ],
    ]);
}
```

Now, we can include our component in any view. You must specify 3 parameters, 
`starting-hour`, `ending-hour` and `interval`. These parameters represent the times of a day the grid will render
and how many divisions per hour it will display. (`interval` must be in minutes and less than `60`)

Example

```blade
<livewire:appointments-grid
    starting-hour="8"
    ending-hour="19"
    interval="15"
/>
``` 

You should include scripts with `@livewireResourceTimeGrid` to enable drag and drop which is turned on by default.
You must include them after `@livewireScripts`

```blade
@livewireScripts
@livewireResourceTimeGridScripts
``` 

This will render a grid starting from 8am til 7pm inclusive with time slots of 15 minutes.

![example](https://github.com/asantibanez/livewire-resource-time-grid/raw/master/example.png)

By default, the component uses all the available width and height. 
You can constrain it to use a specific set of dimensions with a wrapper element.

## Advanced Usage

### UI customization
You can customize the behavior of the component with the following properties when rendering on a view:

- `resource-column-header-view` which can be any `blade.php` view that renders information of a resource. 
This view will be injected with a `$resource` variable holding its data.
- `event-view` which can be any `blade.php` view that will be used to render the event card. 
This view will be injected with a `$event` variable holding its data. 
- `resource-column-header-height-in-rems` and `hour-height-in-rems` can be used to customize the height of each resource view or time slot 
respectively. Defaults used are `4` and `8` respectively. These will be used as `rem` values.
- `before-grid-view` and `after-grid-view` can be any `blade.php` views that can be rendered before or after
the grid itself. These can be used to add extra features to your component using Livewire.

Example

```blade
<livewire:appointments-grid
    starting-hour="8"
    ending-hour="19"
    interval="15"
    resource-column-header-view="path/to/view/staring/from/views/folder"
    event-view="path/to/view/staring/from/views/folder"
    resource-column-header-height-in-rems="4"
    hour-height-in-rems="8"
    before-grid-view="path/to/view/staring/from/views/folder"
    after-grid-view="path/to/view/staring/from/views/folder"
/>
```

> [!CAUTION]
> UI Customisation has not been tested with the new 383 implementation; care should be taken to reproduce the necessary parts within
> each view to ensure your custom views preserve required functionality.

### Interaction customization

You can override the following methods to add interactivity to your component

```php
public function hourSlotClick($resourceId, $hour, $slot)
{
    // This event is triggered when a time slot is clicked.// 
    // You'll get the resource id as well as the hour and minute
    // clicked by the user
}

public function onEventClick($event)
{
    // This event will fire when an event is clicked. You will get the event that was
    // clicked by the user
}

public function onEventDropped($eventId, $resourceId, $hour, $slot)
{
    // This event will fire when an event is dragged and dropped into another time slot
    // You will get the event id, the new resource id + hour + minute where it was
    // dragged to
}
```

You can also override how events and resources are matched instead of using a `resource_id` and `id` respectively.
To do this, you must override the following method

```php
public function isEventForResource($event, $resource)
{
    // Must return true or false depending if the $resource is the owner of the $event
}
```

The base implementation for this method is 

```php
return $event['resource_id'] == $resource['id'];
```

You can customize it as you need. 👍 

## 383's Additions

As well as bringing the code up to date, we have also added a few features which may be useful.

### Reactive properties

In order to facilitate live updating of key layout features on the fly, we have made the following fields reactive:

* `startingHour`
* `endingHour`
* `interval`
* `hourHeightInRems`

For this to work correctly, you will need to arrange a few things:

* When one of these fields is changed, your app will need to dispatch a `onRefreshResourceTimeGrid` livewire event to refresh the time grid component
* If you are using drag-to-scroll or drag-to-create, you will need to re-run the initialisation scripts using something like this:

```php
@script
<script>
    // This is required to reinitialise the component when the settings are changed
    window.Livewire.on('onLivewireResourceTimeGridMounted', () => {
        initDragToScroll();
    });

</script>
@endscript
```

### Drag-to-scroll & Drag-to-create

We have added a feature which allows you to drag the grid to scroll it. This is particularly useful when you have a large number of resources and events, and you want to be able to scroll through them quickly. This works by scrolling the grid horizontally, or the whole page vertically if you are at the top or bottom of the grid. This is achieved by holding the right mouse button and moving the mouse, or by holding the shift key while dragging.

There is also a hover-over tooltip that repeats the column header and the time slot so it's easy to see where you are when the page is scrolled.

In addition you can create new items by dragging from the top of the grid to the bottom. This will create a new event in the resource you are dragging from, with the start and end times corresponding to the time slot you are dragging to. Use the middle button, or hold control while dragging, to use this feature.

These features must be individually enabled like this:

```php
    // Render your component
    @livewire(\App\Livewire\MyLivewireTimeGrid::class, [
        ...    
        'dragToScroll'=> true,
        'dragToCreate' => true,
    ])

    // You will need to load the relevant scripts after the component is initially rendered:
    @livewireResourceTimeGridDragToScroll
    @script
    <script>
        // This is required to reinitialise the component when the settings are changed
        window.Livewire.on('onLivewireResourceTimeGridMounted', () => {
            initDragToScroll();
        });

    </script>
    @endscript

```

### Per-event styling

The original version of this package required a single set of styles from a single function; this was not flexible enough for our requirements at 383
as we needed to have different colours and content for different events.

Therefore you can now provide a lot more details in the event array; here is an example from our implementation:

```php
    public function events()
    {
        return $itemCollection->map(fn (ItineraryItem $event) => [
            # These items are standard as in the original package:
            'id' => $event->id,
            'resource_id' => intval($event->start_at->format('Ymd')),
            'title' => $event->name,
            'starts_at' => $event->start_at,
            'ends_at' => $event->end_at,
            # These are the additional properties we have added:
            'header' => $event->start_at->format('H:i') . " - {$event->name}",
            'header_class' => 'bg-blue-500 text-white p-1 text-xs font-bold',
            'body' => $event->description,
            'body_class' => 'bg-blue-100 p-1 text-xs whitespace-pre overflow',
            'footer' => " ", # If this is empty, the footer won't be used, so we use a space to ensure it is rendered
            'footer_class' => 'bg-blue-500 text-white p-1 text-xs font-bold',
        ]);
        return $return;
    }
```

By including this information in the event array, you can now style the header, body and footer of each event individually. This allows you to have different colours, fonts, sizes, etc. for each event, and to include additional information in the event card.

## Original Information

> [!WARNING]
> None of the following information has been explicitly updated in the 383 environment, and as such
> everything that follows should be considered as potentially out of date information, and may not
> apply.


### Testing

``` bash
composer test
```

### Todo

* Add drag-to-resize functionality
* Fully remove old implementation quirks and redundencies
* Ensure package is self-sufficient and does not rely on external scripts

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email santibanez.andres@gmail.com instead of using the issue tracker.

## Credits

- [Andrés Santibáñez](https://github.com/asantibanez)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
