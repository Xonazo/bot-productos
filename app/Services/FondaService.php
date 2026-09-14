<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class FondaService
{
    public function searchNearby(float $lat, float $lng, int $radiusMeters = 50000, int $limit = 5, int $offset = 0)
    {
        return DB::table('fondas')
            ->selectRaw('id, name, address, dates, prices, artists')
            ->selectRaw('ST_Y(location::geometry) as lat, ST_X(location::geometry) as lng')
            ->selectRaw(
                'ST_Distance(location, ST_SetSRID(ST_MakePoint(?, ?), 4326)) as distance_m',
                [$lng, $lat]
            )
            ->whereNotNull('location')
            ->whereRaw(
                'ST_DWithin(location, ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)',
                [$lng, $lat, $radiusMeters]
            )
            ->orderBy('distance_m', 'asc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function countNearby(float $lat, float $lng, int $radiusMeters = 50000): int
    {
        return DB::table('fondas')
            ->whereNotNull('location')
            ->whereRaw(
                'ST_DWithin(location, ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)',
                [$lng, $lat, $radiusMeters]
            )
            ->count();
    }

    public function all(int $limit = 5, int $offset = 0)
    {
        return DB::table('fondas')
            ->selectRaw('id, name, address, dates, prices, artists')
            ->selectRaw('ST_Y(location::geometry) as lat, ST_X(location::geometry) as lng')
            ->orderBy('name', 'asc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function countAll(): int
    {
        return DB::table('fondas')->count();
    }

    public function allOrderedByDistance(float $lat, float $lng, int $limit = 5, int $offset = 0)
    {
        return DB::table('fondas')
            ->selectRaw('id, name, address, dates, prices, artists')
            ->selectRaw('ST_Y(location::geometry) as lat, ST_X(location::geometry) as lng')
            ->selectRaw(
                'ST_Distance(location, ST_SetSRID(ST_MakePoint(?, ?), 4326)) as distance_m',
                [$lng, $lat]
            )
            ->orderByRaw('location IS NULL, distance_m ASC')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }
}