<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     type="object",
 *     title="Transaction",
 *     description="Transaction Model",
 *     properties={
 *         @OA\Property(property="user_id", type="integer", example=1),
 *         @OA\Property(property="product_id", type="integer", example=1),
 *         @OA\Property(property="type", type="string", example="purchase"),
 *         @OA\Property(property="transaction_date", type="DateTime", example=01-01-2000),
 *         @OA\Property(property="price_per_unit", type="number", format="float", example=100.00),
 *         @OA\Property(property="quantity", type="integer", example=20)
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2023-09-20T14:20:00Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-09-20T14:20:00Z")
 *     }
 * )
 */
class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'type', 'transaction_date', 'quantity', 'price_per_unit', 'created_at', 'updated_at'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
