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

                            element.addEventListener('mousedown', (event) => {
                                event.preventDefault();
                                event.stopPropagation();
                                if (event.button == 0) {
                                    // Drag to create
                                    window.dragToScroll = null;
                                    window.dragToCreate = {
                                        element: element
                                        , x: event.clientX
                                        , y: event.clientY
                                    , };
                                    console.log('drag to create');
                                    // return false;
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
                                // return false;
                            });

                            element.addEventListener('mouseup', (event) => {
                                event.preventDefault();
                                event.stopPropagation();
                                window.dragToScroll = null;
                                window.dragToCreate = null;
                                console.log('drag to scroll end');
                                return false;
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



                                    console.log('drag to scroll move', ydiff, window.dragToScroll.scrollTop);
                                    return false;
                                }
                                if (window.dragToCreate) {
                                    console.log('drag to create move');
                                    return false;
                                }
                            });
                        });
                        scrollElement.querySelectorAll('[data-resource-id]').forEach(element => {
                            element.addEventListener('mouseover', (event) => {
                                const rect = element.getBoundingClientRect();
                                tip = scrollElement.querySelector('.drag-to-scroll-pointer-note');
                                tip.style.display = 'block';
                                tip.style.left = rect.left + rect.width + 'px';
                                tip.style.top = rect.top + rect.height + 'px';
                                tip.innerHTML = element.getAttribute('data-hour') + ':' + element.getAttribute('data-slot') + '<br/>' + element.getAttribute('data-resource-title');
                            });
                            element.addEventListener('mouseout', (event) => {
                                tip = scrollElement.querySelector('.drag-to-scroll-pointer-note');
                                tip.style.display = 'none';
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
