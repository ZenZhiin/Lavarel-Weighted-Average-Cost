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
        $transactions = Transaction::orderBy('date', 'asc')->get();
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
            'product_id' => 'required|integer',
            'type' => 'required|in:purchase,sale',
            'transaction_date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric|min:0',
        ]);

        // Check if the product ID is 0, indicating a new product should be created
        if ($request->input('product_id') == 0) {
            // Create a new product
            $product = Product::create([
                'name' => 'New Product', // You might want to adjust the name generation logic
                'price' => $request->input('price'), // Set the price if it's a purchase
            ]);
        } else {
            // Find the product by ID
            $product = Product::find($request->input('product_id'));

            if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }
        }

        if ($request->type == 'purchase') {
            $product->updateAveragePrice($request->quantity, $request->price_per_unit);
        } elseif ($request->type == 'sale') {
            if ($product->inventory < $request->quantity) {
                return response()->json(['error' => 'Not enough inventory for sale.'], 400);
            }

            $product->inventory -= $request->quantity;
            $product->save();
        }

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
        $transactions = Transaction::where('product_id', $product_id)->orderBy('date', 'asc')->get();

        if ($transactions->isEmpty()) {
            return response()->json(['message' => 'No transactions found for this product'], 404);
        }

        return response()->json($transactions, 200);
    }

    /**
     * Update the specified transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
            'date' => 'required|date',
            'type' => 'required|in:purchase,sale',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find the transaction by ID
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        // Check if a transaction already exists for the given date (except for the current transaction)
        $existingTransaction = Transaction::where('date', $request->date)
            ->where('id', '!=', $id)
            ->first();
        if ($existingTransaction) {
            return response()->json(['message' => 'A transaction already exists for the given date.'], 400);
        }

        // Update the transaction with the validated data
        $transaction->product_id = $request->product_id;
        $transaction->quantity = $request->quantity;
        $transaction->price = $request->price;
        $transaction->date = $request->date;
        $transaction->type = $request->type;
        $transaction->save();

        // Update the product's price and quantity based on the transaction type
        $product = Product::find($transaction->product_id);
        if ($transaction->type === 'purchase') {
            // Update product price to new average price
            $totalQuantity = $product->quantity + $transaction->quantity;
            $totalCost = ($product->price * $product->quantity) + ($transaction->price * $transaction->quantity);
            $product->price = $totalCost / $totalQuantity;
            $product->quantity += $transaction->quantity;
        } else if ($transaction->type === 'sale') {
            // Decrease the product quantity
            $product->quantity -= $transaction->quantity;
        }
        $product->save();

        return response()->json(['message' => 'Transaction updated successfully.']);
    }

     /**
     * Remove the specified transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Find the transaction by ID
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        // Update the product's quantity based on the transaction type before deletion
        $product = Product::find($transaction->product_id);
        if ($transaction->type === 'purchase') {
            // Reduce the quantity and adjust price if necessary
            $product->quantity -= $transaction->quantity;
        } else if ($transaction->type === 'sale') {
            // Increase the quantity back
            $product->quantity += $transaction->quantity;
        }
        $product->save();

        // Delete the transaction
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully.']);
    }
}
