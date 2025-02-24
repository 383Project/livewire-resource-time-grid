<?php

namespace Team383\LivewireResourceTimeGrid;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class LivewireResourceTimeGridServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'livewire-resource-time-grid');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => $this->app->resourcePath('views/vendor/livewire-resource-time-grid'),
            ], 'livewire-resource-time-grid');
        }

        Blade::directive('livewireResourceTimeGridScripts', function () {
            return <<<'HTML'
            <script>
                function onLivewireResourceTimeGridEventDragStart(event, eventId) {
                    event.dataTransfer.setData('id', eventId);
                }

                function onLivewireResourceTimeGridEventDragEnter(event, componentId, resourceId, hour, slot) {
                    event.stopPropagation();
                    event.preventDefault();

                    let element = document.getElementById(`${componentId}-${resourceId}-${hour}-${slot}`);
                    element.className = element.className + ' bg-indigo-100 ';
                }

                function onLivewireResourceTimeGridEventDragLeave(event, componentId, resourceId, hour, slot) {
                    event.stopPropagation();
                    event.preventDefault();

                    let element = document.getElementById(`${componentId}-${resourceId}-${hour}-${slot}`);
                    element.className = element.className.replace('bg-indigo-100', '');
                }

                function onLivewireResourceTimeGridEventDragOver(event) {
                    event.stopPropagation();
                    event.preventDefault();
                }

                function onLivewireResourceTimeGridEventDrop(event, componentId, resourceId, hour, slot) {
                    event.stopPropagation();
                    event.preventDefault();

                    let element = document.getElementById(`${componentId}-${resourceId}-${hour}-${slot}`);
                    element.className = element.className.replace('bg-indigo-100', '');

                    const eventId = event.dataTransfer.getData('id');
                    // window.livewire.components.findComponent(componentId).call('onEventDropped', eventId, resourceId, hour, slot);
                    Livewire.dispatch('onEventDropped', [eventId, resourceId, hour, slot]);
                }

            </script>
HTML;
        });

        Blade::directive('livewireResourceTimeGridDragToScroll', function () {
            return <<<'HTML'
            <script>
                function initDragToScroll() {

                    document.querySelectorAll('.drag-to-scroll').forEach(scrollElement => {
                        scrollElement.querySelectorAll('.drag-to-scroll .overflow-x-auto').forEach(element => {
                            element.addEventListener('contextmenu', (event) => {
                                event.preventDefault();
                                event.stopPropagation();
                                return false;
                            });


                            element.addEventListener('mouseup', (event) => {
                                tip = scrollElement.querySelector('.drag-to-create-pointer-note');
                                if (tip) {
                                    tip.style.display = 'none';
                                }

                                if (window.dragToScroll) {
                                    event.preventDefault();
                                    event.stopPropagation();
                                    console.log('drag to scroll end');
                                    window.dragToCreate = null;
                                    window.dragToScroll = null;
                                    return false;
                                } else if (window.dragToCreate) {
                                    event.preventDefault();
                                    event.stopPropagation();
                                    console.log('drag to create end', window.dragToCreate);
                                    const {
                                        fromId
                                        , fromHour
                                        , fromSlot
                                        , toId
                                        , toHour
                                        , toSlot
                                        , slots
                                    } = window.dragToCreate;
                                    Livewire.dispatch('drag-to-create', [
                                        fromId
                                        , fromHour
                                        , fromSlot
                                        , toId
                                        , toHour
                                        , toSlot
                                        , slots
                                    ]);
                                    window.dragToCreate = null;
                                    return false;
                                }


                            });

                            element.addEventListener('mouseleave', (event) => {
                                event.preventDefault();
                                event.stopPropagation();
                                window.dragToScroll = null;
                                window.dragToCreate = null;
                                console.log('drag to scroll leave');
                                return false;
                            });

                            element.addEventListener('mousemove', (event) => {
                                event.preventDefault();
                                event.stopPropagation();
                                if (window.dragToScroll) {
                                    let xdiff = window.dragToScroll.x - event.clientX;
                                    let ydiff = window.dragToScroll.y - event.clientY;
                                    window.dragToScroll.element.scrollLeft = window.dragToScroll.scrollLeft + xdiff;
                                    window.scrollBy(0, ydiff);

                                    window.dragToScroll = {
                                        ...window.dragToScroll
                                        , x: event.clientX
                                        , y: event.clientY
                                        , scrollLeft: element.scrollLeft
                                        , scrollTop: body.scrollTop
                                    , };

                                    tip = scrollElement.querySelector('.drag-to-scroll-pointer-note');
                                    tip.style.display = 'none';

                                    console.log('drag to scroll move', window.dragToScroll);
                                    return false;
                                }
                            });
                            element.querySelectorAll('[data-resource-id]').forEach(dataElement => {
                                const rect = dataElement.getBoundingClientRect();
                                const hour = dataElement.getAttribute('data-hour');
                                const slot = dataElement.getAttribute('data-slot');
                                const title = dataElement.getAttribute('data-resource-title');
                                const id = dataElement.getAttribute('data-resource-id');

                                dataElement.addEventListener('mousedown', (event) => {
                                    event.preventDefault();
                                    event.stopPropagation();

                                    console.log('drag to scroll start', event.button);
                                    if (event.button == 0) {
                                        // Drag to create
                                        window.dragToScroll = null;
                                        window.dragToCreate = {
                                            element: dataElement
                                            , fromHour: hour
                                            , fromSlot: slot
                                            , fromId: id
                                            , toHour: null
                                            , toSlot: null
                                            , toId: null
                                        };
                                        console.log('drag to create');
                                        return false;
                                    }
                                    body = document.getElementsByTagName('body')[0];
                                    window.dragToCreate = null;
                                    window.dragToScroll = {
                                        element: element
                                        , body
                                        , x: event.clientX
                                        , y: event.clientY
                                        , scrollLeft: element.scrollLeft
                                        , scrollTop: body.scrollTop
                                    , };
                                    console.log('drag to scroll', body);
                                    return false;
                                });

                                dataElement.addEventListener('mouseover', (event) => {
                                    tip = scrollElement.querySelector('.drag-to-scroll-pointer-note');
                                    tip.style.display = 'block';
                                    tip.style.left = rect.left + rect.width + 'px';
                                    tip.style.top = rect.top + rect.height + 'px';
                                    tip.innerHTML = hour + ':' + slot + '<br/>' + title;

                                    if (window.dragToCreate) {
                                        event.preventDefault();
                                        event.stopPropagation();
                                        let startRect = window.dragToCreate.element.getBoundingClientRect();
                                        let endRect = dataElement.getBoundingClientRect();

                                        let height = endRect.top - startRect.top + endRect.height;

                                        const tip = scrollElement.querySelector('.drag-to-create-pointer-note');
                                        console.log('tip', tip, startRect, endRect, height);
                                        tip.style.top = startRect.top + 'px';
                                        tip.style.left = startRect.left - 5 + 'px';
                                        tip.style.width = '5px';
                                        tip.style.height = height + 'px';
                                        tip.style.display = 'block';

                                        const slots = height / startRect.height;
                                        window.dragToCreate = {
                                            ...window.dragToCreate
                                            , toHour: hour
                                            , toSlot: slot
                                            , toId: id
                                            , slots
                                        }
                                        return false;
                                    }

                                });
                                dataElement.addEventListener('mouseout', (event) => {
                                    tip = scrollElement.querySelector('.drag-to-scroll-pointer-note');
                                    tip.style.display = 'none';
                                });
                            });

                        });

                    });

                }

                initDragToScroll();
            </script>
HTML;
        });
    }

    public function register()
    {
        //
    }
}
