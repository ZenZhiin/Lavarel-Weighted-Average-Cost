<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    // Display a listing of the transactions
    public function index()
    {
        $transactions = Transaction::with('product')->where('user_id', Auth::id())->get();
        return response()->json($transactions);
    }

    // Store a new transaction (purchase or sale)
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:purchase,sale',
            'transaction_date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric|min:0',
        ]);

        $product = Product::find($request->product_id);

        // Check and update inventory based on transaction type
        if ($request->type == 'sale') {
            // Check if there's enough inventory for the sale
            if ($product->inventory < $request->quantity) {
                return response()->json(['error' => 'Not enough inventory for sale.'], 400);
            }

            // Decrease product inventory
            $product->inventory -= $request->quantity;
        } elseif ($request->type == 'purchase') {
            // Increase product inventory
            $product->inventory += $request->quantity;
        }

        // Save the product with updated inventory
        $product->save();

        // Create the transaction
        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'type' => $request->type,
            'transaction_date' => $request->transaction_date,
            'quantity' => $request->quantity,
            'price_per_unit' => $request->price_per_unit,
        ]);

        return response()->json($transaction, 201);
    }
}
