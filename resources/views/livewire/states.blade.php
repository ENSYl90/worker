@section('title', __('States'))
<div>

    <x-baseview title="{{ __('States') }}" showNew="true">
        <livewire:tables.state-table />
    </x-baseview>

    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal confirmText="{{ __('Save') }}" action="save">
            <p class="text-xl font-semibold">{{ __('Create State') }}</p>
            <x-select title="{{ __('Country') }}" :options='$countries' name="country_id" :defer="false" />
            <x-input title="{{ __('Name') }}" name="name" placeholder="" />
        </x-modal>
    </div>

    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal confirmText="{{ __('Update') }}" action="update">

            <p class="text-xl font-semibold">{{ __('Edit State') }}</p>
            <x-select title="{{ __('Country') }}" :options='$countries' name="country_id" :defer="false" />
            <x-input title="{{ __('Name') }}" name="name" placeholder="" />


        </x-modal>
    </div>

    {{-- Boundary Generator Component --}}
    <livewire:boundary-generator />

    {{-- details modal --}}
    <div x-data="{ open: @entangle('showDetails') }">
        @if ($selectedModel != null)
            <x-modal>

                <p class="text-xl font-semibold">{{ ucfirst($selectedModel->name) }}</p>
                <hr class="my-2" />
                {{-- map view of the counter boundaries --}}
                <div>
                    @if ($selectedModel->boundaries)
                        <div id="countryMap" class="w-full border border-gray-300 rounded-sm" style="height: 40vh;">
                        </div>
                    @else
                        <div class="text-gray-500 text-center py-8">{{ __('No boundary data available') }}</div>
                    @endif
                </div>

            </x-modal>
        @endif
    </div>


</div>

@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $(function() {
            let countryMap = null;

            function initCountryMap(boundaries) {
                console.log("boundaries", boundaries);
                if (countryMap) {
                    countryMap.remove();
                }

                countryMap = L.map('countryMap').setView([0, 0], 2);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(countryMap);

                if (boundaries && boundaries.length > 0) {
                    displayCountryBoundaries(boundaries);
                }
            }

            function displayCountryBoundaries(boundaries) {
                const boundsGroup = new L.LatLngBounds();

                if (isMultiPolygon(boundaries)) {
                    boundaries.forEach(polygon => {
                        if (Array.isArray(polygon) && polygon.length > 0) {
                            const coords = polygon.map(coord => [coord[1], coord[0]]);
                            const leafletPolygon = L.polygon(coords, {
                                color: '#e74c3c',
                                weight: 2,
                                fillOpacity: 0.3,
                                fillColor: '#e74c3c'
                            }).addTo(countryMap);

                            coords.forEach(coord => boundsGroup.extend(coord));
                        }
                    });
                } else {
                    const coords = boundaries.map(coord => [coord[1], coord[0]]);
                    const leafletPolygon = L.polygon(coords, {
                        color: '#e74c3c',
                        weight: 2,
                        fillOpacity: 0.3,
                        fillColor: '#e74c3c'
                    }).addTo(countryMap);

                    coords.forEach(coord => boundsGroup.extend(coord));
                }

                if (boundsGroup.isValid()) {
                    countryMap.fitBounds(boundsGroup, {
                        padding: [20, 20]
                    });
                }
            }

            function isMultiPolygon(boundaries) {
                return Array.isArray(boundaries) &&
                    boundaries.length > 0 &&
                    Array.isArray(boundaries[0]) &&
                    Array.isArray(boundaries[0][0]) &&
                    Array.isArray(boundaries[0][0][0]);
            }


            livewire.on('initMapBoundaryDetail', (boundaries) => {
                if (boundaries && boundaries.length > 0) {
                    initCountryMap(boundaries);
                }
            });


        });
    </script>
@endpush
