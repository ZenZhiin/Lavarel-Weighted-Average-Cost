<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
