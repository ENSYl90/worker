<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Polygon Service Class
 *
 * Provides polygon validation and point-in-polygon functionality
 * for geographic coordinate operations in Laravel applications.
 */
class PolygonService
{
    /**
     * Check if an array of lat/lng points forms a valid polygon
     *
     * @param array $points Array of points in format [['lat' => float, 'lng' => float], ...]
     * @return array Returns validation result with status and message
     * @throws InvalidArgumentException When points array is not valid
     */
    public function validatePolygon(array $points): array
    {
        if (empty($points)) {
            throw new InvalidArgumentException('Points array cannot be empty');
        }

        // Check minimum number of points
        if (count($points) < 3) {
            return [
                'valid' => false,
                'message' => 'A polygon must have at least 3 points',
                'error_code' => 'INSUFFICIENT_POINTS'
            ];
        }

        // Validate each point has required lat/lng keys
        foreach ($points as $index => $point) {
            $validation = $this->validatePoint($point, $index);
            if (!$validation['valid']) {
                return $validation;
            }
        }

        // Check if polygon is closed (first and last points should be the same for a proper polygon)
        $firstPoint = $points[0];
        $lastPoint = $points[count($points) - 1];

        $isClosed = $this->arePointsEqual($firstPoint, $lastPoint);

        // Check for self-intersection (basic check)
        if ($this->hasSelfIntersection($points)) {
            return [
                'valid' => false,
                'message' => 'Polygon has self-intersecting edges',
                'error_code' => 'SELF_INTERSECTION'
            ];
        }

        // Check if all points are collinear (would make a line, not a polygon)
        if ($this->arePointsCollinear($points)) {
            return [
                'valid' => false,
                'message' => 'All points are collinear - this forms a line, not a polygon',
                'error_code' => 'COLLINEAR_POINTS'
            ];
        }

        // Calculate polygon area to ensure it's not degenerate
        $area = $this->calculatePolygonArea($points);
        if (abs($area) < 0.000001) {
            return [
                'valid' => false,
                'message' => 'Polygon has zero or near-zero area',
                'error_code' => 'ZERO_AREA'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Valid polygon',
            'data' => [
                'closed' => $isClosed,
                'point_count' => count($points),
                'area' => abs($area),
                'clockwise' => $area < 0
            ]
        ];
    }

    /**
     * Check if a point is inside a polygon using ray casting algorithm
     *
     * @param array $point Point to check ['lat' => float, 'lng' => float]
     * @param array $polygon Array of polygon points
     * @return bool True if point is inside polygon
     * @throws InvalidArgumentException When inputs are invalid
     */
    public function isPointInPolygon(array $point, array $polygon): bool
    {
        // Validate inputs
        $pointValidation = $this->validatePoint($point);
        if (!$pointValidation['valid']) {
            throw new InvalidArgumentException('Invalid point: ' . $pointValidation['message']);
        }

        $polygonValidation = $this->validatePolygon($polygon);
        if (!$polygonValidation['valid']) {
            throw new InvalidArgumentException('Invalid polygon: ' . $polygonValidation['message']);
        }

        return $this->raycastingAlgorithm($point, $polygon);
    }

    /**
     * Get detailed information about a polygon
     *
     * @param array $polygon
     * @return array
     */
    public function getPolygonInfo(array $polygon): array
    {
        $validation = $this->validatePolygon($polygon);

        if (!$validation['valid']) {
            return $validation;
        }

        $bounds = $this->getBoundingBox($polygon);
        $centroid = $this->calculateCentroid($polygon);
        $perimeter = $this->calculatePerimeter($polygon);

        return [
            'valid' => true,
            'validation' => $validation,
            'bounds' => $bounds,
            'centroid' => $centroid,
            'perimeter' => $perimeter,
            'statistics' => [
                'min_lat' => $bounds['min_lat'],
                'max_lat' => $bounds['max_lat'],
                'min_lng' => $bounds['min_lng'],
                'max_lng' => $bounds['max_lng'],
                'width' => $bounds['max_lng'] - $bounds['min_lng'],
                'height' => $bounds['max_lat'] - $bounds['min_lat']
            ]
        ];
    }

    /**
     * Validate a single point
     *
     * @param array $point
     * @param int|null $index
     * @return array
     */
    private function validatePoint(array $point, ?int $index = null): array
    {
        $indexText = $index !== null ? "at index {$index}" : '';

        if (!isset($point['lat']) || !isset($point['lng'])) {
            return [
                'valid' => false,
                'message' => "Point {$indexText} is missing lat or lng",
                'error_code' => 'MISSING_COORDINATES'
            ];
        }

        // Validate lat/lng are numeric
        if (!is_numeric($point['lat']) || !is_numeric($point['lng'])) {
            return [
                'valid' => false,
                'message' => "Point {$indexText} has non-numeric lat or lng",
                'error_code' => 'NON_NUMERIC_COORDINATES'
            ];
        }

        // Validate lat/lng ranges
        if ($point['lat'] < -90 || $point['lat'] > 90) {
            return [
                'valid' => false,
                'message' => "Point {$indexText} has invalid latitude (must be between -90 and 90)",
                'error_code' => 'INVALID_LATITUDE'
            ];
        }

        if ($point['lng'] < -180 || $point['lng'] > 180) {
            return [
                'valid' => false,
                'message' => "Point {$indexText} has invalid longitude (must be between -180 and 180)",
                'error_code' => 'INVALID_LONGITUDE'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Ray casting algorithm implementation
     *
     * @param array $point
     * @param array $polygon
     * @return bool
     */
    public function raycastingAlgorithm(array $point, array $polygon): bool
    {
        $x = $point['lng'];
        $y = $point['lat'];

        $n = count($polygon);
        $inside = false;

        $p1x = $polygon[0]['lng'];
        $p1y = $polygon[0]['lat'];

        for ($i = 1; $i <= $n; $i++) {
            $p2x = $polygon[$i % $n]['lng'];
            $p2y = $polygon[$i % $n]['lat'];

            if ($y > min($p1y, $p2y)) {
                if ($y <= max($p1y, $p2y)) {
                    if ($x <= max($p1x, $p2x)) {
                        if ($p1y != $p2y) {
                            $xinters = ($y - $p1y) * ($p2x - $p1x) / ($p2y - $p1y) + $p1x;
                        }
                        if ($p1x == $p2x || $x <= $xinters) {
                            $inside = !$inside;
                        }
                    }
                }
            }
            $p1x = $p2x;
            $p1y = $p2y;
        }

        return $inside;
    }

    /**
     * Check for basic self-intersection in polygon
     *
     * @param array $points
     * @return bool
     */
    private function hasSelfIntersection(array $points): bool
    {
        $n = count($points);

        if ($n < 4) {
            return false;
        }

        for ($i = 0; $i < $n; $i++) {
            $p1 = $points[$i];
            $p2 = $points[($i + 1) % $n];

            for ($j = $i + 2; $j < $n; $j++) {
                if ($j == $n - 1 && $i == 0) continue;

                $p3 = $points[$j];
                $p4 = $points[($j + 1) % $n];

                if ($this->doLinesIntersect($p1, $p2, $p3, $p4)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if two line segments intersect
     *
     * @param array $p1
     * @param array $p2
     * @param array $p3
     * @param array $p4
     * @return bool
     */
    private function doLinesIntersect(array $p1, array $p2, array $p3, array $p4): bool
    {
        $d1 = $this->orientation($p3, $p4, $p1);
        $d2 = $this->orientation($p3, $p4, $p2);
        $d3 = $this->orientation($p1, $p2, $p3);
        $d4 = $this->orientation($p1, $p2, $p4);

        if ((($d1 > 0 && $d2 < 0) || ($d1 < 0 && $d2 > 0)) &&
            (($d3 > 0 && $d4 < 0) || ($d3 < 0 && $d4 > 0))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Calculate orientation of three points
     *
     * @param array $p
     * @param array $q
     * @param array $r
     * @return float
     */
    private function orientation(array $p, array $q, array $r): float
    {
        return ($q['lat'] - $p['lat']) * ($r['lng'] - $q['lng']) -
            ($q['lng'] - $p['lng']) * ($r['lat'] - $q['lat']);
    }

    /**
     * Check if all points are collinear
     *
     * @param array $points
     * @return bool
     */
    private function arePointsCollinear(array $points): bool
    {
        $n = count($points);

        if ($n < 3) {
            return true;
        }

        $p1 = $points[0];
        $p2 = $points[1];

        for ($i = 2; $i < $n; $i++) {
            $p3 = $points[$i];

            $crossProduct = ($p2['lat'] - $p1['lat']) * ($p3['lng'] - $p1['lng']) -
                ($p2['lng'] - $p1['lng']) * ($p3['lat'] - $p1['lat']);

            if (abs($crossProduct) > 0.000001) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if two points are equal (within tolerance)
     *
     * @param array $p1
     * @param array $p2
     * @return bool
     */
    private function arePointsEqual(array $p1, array $p2): bool
    {
        return abs($p1['lat'] - $p2['lat']) < 0.000001 &&
            abs($p1['lng'] - $p2['lng']) < 0.000001;
    }

    /**
     * Calculate polygon area using shoelace formula
     *
     * @param array $points
     * @return float
     */
    private function calculatePolygonArea(array $points): float
    {
        $n = count($points);
        $area = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $area += $points[$i]['lng'] * $points[$j]['lat'];
            $area -= $points[$j]['lng'] * $points[$i]['lat'];
        }

        return $area / 2.0;
    }

    /**
     * Calculate bounding box of polygon
     *
     * @param array $points
     * @return array
     */
    private function getBoundingBox(array $points): array
    {
        $minLat = $maxLat = $points[0]['lat'];
        $minLng = $maxLng = $points[0]['lng'];

        foreach ($points as $point) {
            $minLat = min($minLat, $point['lat']);
            $maxLat = max($maxLat, $point['lat']);
            $minLng = min($minLng, $point['lng']);
            $maxLng = max($maxLng, $point['lng']);
        }

        return [
            'min_lat' => $minLat,
            'max_lat' => $maxLat,
            'min_lng' => $minLng,
            'max_lng' => $maxLng
        ];
    }

    /**
     * Calculate centroid of polygon
     *
     * @param array $points
     * @return array
     */
    private function calculateCentroid(array $points): array
    {
        $centroidLat = 0;
        $centroidLng = 0;
        $area = $this->calculatePolygonArea($points);
        $n = count($points);

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $cross = $points[$i]['lng'] * $points[$j]['lat'] - $points[$j]['lng'] * $points[$i]['lat'];
            $centroidLat += ($points[$i]['lat'] + $points[$j]['lat']) * $cross;
            $centroidLng += ($points[$i]['lng'] + $points[$j]['lng']) * $cross;
        }

        $area6 = 6 * $area;
        return [
            'lat' => $centroidLat / $area6,
            'lng' => $centroidLng / $area6
        ];
    }

    /**
     * Calculate perimeter of polygon
     *
     * @param array $points
     * @return float
     */
    private function calculatePerimeter(array $points): float
    {
        $perimeter = 0;
        $n = count($points);

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $perimeter += $this->haversineDistance($points[$i], $points[$j]);
        }

        return $perimeter;
    }

    /**
     * Calculate distance between two points using Haversine formula
     *
     * @param array $point1
     * @param array $point2
     * @return float Distance in kilometers
     */
    private function haversineDistance(array $point1, array $point2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latDelta = deg2rad($point2['lat'] - $point1['lat']);
        $lngDelta = deg2rad($point2['lng'] - $point1['lng']);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($point1['lat'])) * cos(deg2rad($point2['lat'])) *
            sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
