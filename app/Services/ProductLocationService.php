<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductLocationService
{
    public function create(array $data, float $lat, float $lng): Product
    {
        $id = DB::table('products')->insertGetId([
            'user_id' => $data['user_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? null,
            'telegram_chat_id' => $data['telegram_chat_id'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::statement(
            'UPDATE products SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?',
            [$lng, $lat, $id]
        );

        return Product::findOrFail($id);
    }

public function searchNearby(string $query, float $lat, float $lng, int $radiusMeters = 5000, int $limit = 10, int $offset = 0)
{
    return DB::table('products')
        ->selectRaw('id, name, description, price, created_at')
        ->selectRaw('ST_Y(location::geometry) as lat, ST_X(location::geometry) as lng')
        ->selectRaw(
            'ST_Distance(location, ST_SetSRID(ST_MakePoint(?, ?), 4326)) as distance_m',
            [$lng, $lat]
        )
        ->whereRaw(
            'ST_DWithin(location, ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)',
            [$lng, $lat, $radiusMeters]
        )
        ->where('name', 'ilike', "%{$query}%")
        ->orderBy('distance_m', 'asc')
        ->limit($limit)
        ->offset($offset)
        ->get();
}

public function countNearby(string $query, float $lat, float $lng, int $radiusMeters = 5000): int
{
    return DB::table('products')
        ->whereRaw(
            'ST_DWithin(location, ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)',
            [$lng, $lat, $radiusMeters]
        )
        ->where('name', 'ilike', "%{$query}%")
        ->count();
}


    public function search(string $query, int $limit = 10)
{
    return DB::table('products')
        ->select('id', 'name', 'description', 'price', 'created_at')
        ->where('name', 'ilike', "%{$query}%")
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get();
}
}