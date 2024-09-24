<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     description="Product Model",
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Product A"),
 *         @OA\Property(property="description", type="string", example="Description of Product A"),
 *         @OA\Property(property="price", type="number", format="float", example=100.00),
 *         @OA\Property(property="inventory", type="integer", example=20)
 *     }
 * )
 */
class Product extends Model
{
    protected $fillable = ['name', 'description', 'qty', 'price'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Update average price based on new purchase
    public function updateAveragePrice($quantity, $pricePerUnit)
    {
        // Current total inventory value
        $currentTotalValue = $this->inventory * $this->price;
        
        // Value of new purchase
        $newPurchaseValue = $quantity * $pricePerUnit;
        
        // New total inventory quantity
        $newTotalQuantity = $this->inventory + $quantity;
        
        // New average price
        $newAveragePrice = ($currentTotalValue + $newPurchaseValue) / $newTotalQuantity;
        
        // Update product's price and inventory
        $this->price = $newAveragePrice;
        $this->inventory = $newTotalQuantity;
        $this->save();
    }
}
