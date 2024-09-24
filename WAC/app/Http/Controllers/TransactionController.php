<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *     title="Purchase and Sales Transaction API",
 *     version="1.0.0",
 *     description="API for managing purchase and sale transactions"
 * )
 *
 * @OA\Tag(
 *     name="Transactions",
 *     description="API Endpoints related to purchase and sale transactions"
 * )
 */
class TransactionController extends Controller
{
    // Display a listing of the transactions
    /**
     * @OA\Get(
     *     path="/api/transactions",
     *     summary="Retrieve a list of all transactions",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of transaction list",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Transaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        $transactions = Transaction::with('product')->where('user_id', Auth::id())->get();
        return response()->json($transactions);
    }


    /**
     * @OA\Post(
     *     path="/api/transactions",
     *     summary="Record a new transaction (purchase or sale)",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "date", "quantity", "type"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="date", type="string", format="date", example="2023-09-20"),
     *             @OA\Property(property="quantity", type="integer", example=10),
     *             @OA\Property(property="type", type="string", enum={"purchase", "sale"}, example="purchase"),
     *             @OA\Property(property="price", type="number", format="float", example=100.50, description="Required for purchase transactions")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transaction created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Transaction")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */ 
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

        // Handling Purchase Transactions
        if ($request->type == 'purchase') {
            // Update product's average price and inventory
            $product->updateAveragePrice($request->quantity, $request->price_per_unit);
        } elseif ($request->type == 'sale') {
            // Check if there's enough inventory for the sale
            if ($product->inventory < $request->quantity) {
                return response()->json(['error' => 'Not enough inventory for sale.'], 400);
            }

            // Decrease product inventory
            $product->inventory -= $request->quantity;
            $product->save();
        }

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

    /**
     * @OA\Get(
     *     path="/api/transactions/product/{product_id}",
     *     summary="Retrieve all transactions for a specific product by product ID",
     *     tags={"Transactions"},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         description="ID of the product",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of transactions for the specified product",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Transaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found or no transactions for this product"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function getTransactionsByProductId($product_id)
    {
        // Validate that the product exists
        $transactions = Transaction::where('product_id', $product_id)->get();

        if ($transactions->isEmpty()) {
            return response()->json(['message' => 'No transactions found for this product'], 404);
        }

        return response()->json($transactions, 200);
    }
}
