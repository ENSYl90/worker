---
group: Websocket
title: Driver Location Geohash Events
order: 11
---

# Driver Location Geohash Events

Driver location updates are broadcast in two separate streams:

- `WebsocketDriverLocationUpdatedEvent`: private per-driver channel for direct driver tracking.
- `DriverLocationGeohashUpdatedEvent`: public geohash channel for listening to nearby driver movement by map area.

Use the geohash event when a client needs updates for all drivers inside a location bucket instead of listening to each driver individually.

## Channel Format

```text
drivers.locations.{geohash}.updated
```

Example:

```text
drivers.locations.ey7k.updated
```

The geohash value is calculated from:

- the driver's latest `lat`
- the driver's latest `lng`
- the search radius converted to geohash precision

The backend uses `App\Services\DriverLocationGeohashService` for all geohash logic.

## Event Name

```text
DriverLocationGeohashUpdated
```

When listening with Laravel Echo, prefix custom broadcast names with a dot:

```js
.DriverLocationGeohashUpdated
```

## Payload

The event payload includes the driver location data plus geohash metadata.

Example:

```json
{
  "lat": 5.603716,
  "lng": -0.232861,
  "rotation": 92,
  "vehicle_type_id": 3,
  "geohash": "ey7k",
  "geohash_channel": "drivers.locations.ey7k.updated"
}
```

## Radius To Precision

Clients must compute the same geohash precision as the backend before subscribing.

| Radius in km | Precision |
| ---: | ---: |
| `<= 0.0012` | `10` |
| `<= 0.0047` | `9` |
| `<= 0.038` | `8` |
| `<= 0.152` | `7` |
| `<= 1.2` | `6` |
| `<= 4.9` | `5` |
| `<= 39` | `4` |
| `<= 156` | `3` |
| `<= 1250` | `2` |
| `<= 5000` | `1` |
| otherwise | `9` |

The default production driver-location update flow uses the global `driverSearchRadius` setting. The fake movement command passes its command radius into the geohash event.

## JavaScript Helper

Use this helper in clients so the subscribed channel matches the backend channel.

```js
function geohashPrecisionFromRadiusKm(radiusInKm) {
  if (radiusInKm <= 0.0012) return 10;
  if (radiusInKm <= 0.0047) return 9;
  if (radiusInKm <= 0.038) return 8;
  if (radiusInKm <= 0.152) return 7;
  if (radiusInKm <= 1.2) return 6;
  if (radiusInKm <= 4.9) return 5;
  if (radiusInKm <= 39) return 4;
  if (radiusInKm <= 156) return 3;
  if (radiusInKm <= 1250) return 2;
  if (radiusInKm <= 5000) return 1;

  return 9;
}

function driverLocationGeohashChannel(geohash) {
  return `drivers.locations.${geohash}.updated`;
}
```

## Listen With Laravel Echo

Install or use any geohash encoder that produces standard geohashes. The example below uses a generic `encodeGeohash` function to keep the subscription logic clear.

```js
const mapCenter = {
  lat: 5.603716,
  lng: -0.232861,
};

const radiusInKm = 10;
const precision = geohashPrecisionFromRadiusKm(radiusInKm);
const geohash = encodeGeohash(mapCenter.lat, mapCenter.lng, precision);
const channelName = driverLocationGeohashChannel(geohash);

Echo.channel(channelName)
  .listen('.DriverLocationGeohashUpdated', (event) => {
    console.log('Driver moved inside geohash area', event);
  });
```

## Listen With Pusher JS Or Reverb Protocol

Public channels do not need the `private-` prefix or channel authorization.

```js
const channel = pusher.subscribe(channelName);

channel.bind('DriverLocationGeohashUpdated', (event) => {
  console.log('Driver moved inside geohash area', event);
});
```

## Updating Subscriptions As The Map Moves

When a map viewport changes:

1. Get the new map center latitude and longitude.
2. Use the active search radius to calculate precision.
3. Encode the new center to geohash.
4. If the geohash changed, unsubscribe from the old channel.
5. Subscribe to `drivers.locations.{newGeohash}.updated`.

Example:

```js
let activeChannelName = null;

function subscribeToDriverArea(lat, lng, radiusInKm) {
  const precision = geohashPrecisionFromRadiusKm(radiusInKm);
  const geohash = encodeGeohash(lat, lng, precision);
  const nextChannelName = driverLocationGeohashChannel(geohash);

  if (activeChannelName === nextChannelName) {
    return;
  }

  if (activeChannelName) {
    Echo.leave(activeChannelName);
  }

  activeChannelName = nextChannelName;

  Echo.channel(activeChannelName)
    .listen('.DriverLocationGeohashUpdated', (event) => {
      updateDriverMarker({
        lat: Number(event.lat),
        lng: Number(event.lng),
        rotation: Number(event.rotation || 0),
        vehicleTypeId: event.vehicle_type_id,
      });
    });
}
```

## Backend Reference

The event is dispatched when a driver location is updated through:

- `POST /api/driver/location/sync`
- `php artisan driver:fake-movement`

## Fake Movement Command Example

Use the fake movement command to broadcast driver movement events for existing driver accounts. The command does not create driver users; it randomly selects from drivers already in the system.

Command format:

```bash
php artisan driver:fake-movement --delay={seconds} --loop {count} {lat} -- {lng} {radius}
```

Arguments:

| Argument | Description |
| --- | --- |
| `count` | Number of existing driver accounts to simulate |
| `lat` | Center latitude for the simulated movement area |
| `lng` | Center longitude for the simulated movement area |
| `radius` | Radius in kilometers around the center point |

Options:

| Option | Description |
| --- | --- |
| `--delay=3` | Seconds to wait between movement updates when `--loop` is used |
| `--loop` | Keep moving and broadcasting drivers until the command is stopped |

One-time movement update for 5 existing drivers around Accra:

```bash
php artisan driver:fake-movement 5 5.603716 -- -0.232861 10
```

Continuous movement updates every 2 seconds:

```bash
php artisan driver:fake-movement --delay=2 --loop 5 5.603716 -- -0.232861 10
```

The `--` separator is required when the longitude is negative. Without it, the console treats the longitude as an option.

The command broadcasts:

- `WebsocketDriverLocationUpdatedEvent` on the private per-driver channel
- `DriverLocationGeohashUpdatedEvent` on the public geohash channel

The fake command passes its `radius` argument to the geohash event, so clients should subscribe using the same radius when calculating the geohash channel.

Relevant backend classes:

- `App\Events\DriverLocationGeohashUpdatedEvent`
- `App\Services\DriverLocationGeohashService`
- `App\Http\Controllers\API\DriverLocationSyncController`
- `App\Console\Commands\FakeDriverMovement`
