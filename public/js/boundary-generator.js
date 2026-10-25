// Boundary Generator JavaScript - Starting fresh

$(function () {
    // Listen for Livewire event to initiate boundary generation
    livewire.on('initateGeoModelBoundaryGen', function (modelClass, modelId, name) {
        console.log('Received initateGeoModelBoundaryGen event:', {
            modelClass: modelClass,
            modelId: modelId,
            name: name
        });

        // Show the boundary generator modal
        const boundaryGenerator = document.querySelector('.boundary-generator-livewire');
        if (boundaryGenerator && boundaryGenerator.__x) {
            const alpine = boundaryGenerator.__x.$data;
            alpine.open = true;
            alpine.loading = true;
        }

        // Generate boundaries for the model
        generateBoundaries(modelClass, modelId, name);
    });

    // Generate boundaries function
    async function generateBoundaries(modelClass, modelId, name) {

        try {
            // Determine model type from class
            const modelType = modelClass.toLowerCase().replace(/.*\\/, '');

            console.log(`Generating boundaries for ${modelType}: ${name}`);

            // Call Nominatim API
            const params = new URLSearchParams({
                q: name,
                format: 'json',
                polygon_geojson: '1',
                addressdetails: '1',
                limit: '1'
            });

            const response = await fetch(`https://nominatim.openstreetmap.org/search?${params}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error(`API request failed: ${response.status} ${response.statusText}`);
            }

            const data = await response.json();

            if (!data || data.length === 0) {
                throw new Error(`No results found for: ${name}`);
            }

            const result = data[0];

            if (!result.geojson || !result.geojson.coordinates) {
                throw new Error(`No boundary data available for: ${name}`);
            }

            // Prepare boundary data
            const boundaryData = {
                modelType: modelType,
                name: name,
                boundaries: result.geojson.coordinates
            };

            console.log('✅ Boundaries generated successfully:', boundaryData);
            console.log('📍 Raw boundaries data:', result.geojson.coordinates);

            // Call Livewire to save the boundaries
            livewire.emit('saveGeoModel', boundaryData, modelClass, modelId);
            closeLoadingModal();
        } catch (error) {
            console.error('Error generating boundaries:', error);

            closeLoadingModal();
            //emit error
        }
    }


    function closeLoadingModal() {
        const boundaryGenerator = document.querySelector('.boundary-generator-livewire');
        const alpine = boundaryGenerator && boundaryGenerator.__x ? boundaryGenerator.__x.$data : null;
        // Hide loading
        if (alpine) {
            alpine.loading = false;
            alpine.open = false;
        }
    }



});
