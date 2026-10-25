<?php

namespace App\View\Components;

use Illuminate\View\Component;

class WebSocketStatus extends Component
{
    public $wsUrl;
    public $reconnectInterval;
    public $showTraffic;
    public $position;
    public $floating = true;

    public function __construct(
        $wsUrl = null,
        $reconnectInterval = 5000,
        $showTraffic = true,
        $position = 'top-right',
        $floating = true,
    ) {
        // Get Reverb configuration from environment variables
        $host = env('REVERB_HOST', 'localhost');
        $port = env('REVERB_PORT', 8080);
        $scheme = env('REVERB_SCHEME', 'http'); // === 'https' ? 'wss' : 'ws';
        $appKey = env('REVERB_APP_KEY', '');

        // Build the WebSocket URL for Reverb
        $this->wsUrl = $wsUrl ?? "{$scheme}://{$host}:{$port}/app/{$appKey}";
        $this->reconnectInterval = $reconnectInterval;
        $this->showTraffic = $showTraffic;
        $this->position = $position;
        $this->floating = $floating;
    }

    public function render()
    {
        return view('components.web-socket-status');
    }
}
