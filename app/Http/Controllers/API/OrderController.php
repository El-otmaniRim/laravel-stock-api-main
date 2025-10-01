<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use App\Models\User;
use Illuminate\Support\Facades\Log;


class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return Order::with('items.product')->get();
        } elseif ($user->hasRole('delivery')) {
            return Order::where('delivery_id', $user->id)->with('items.product')->get();
        } else {
            return Order::where('user_id', $user->id)->with('items.product')->get();
        }
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user = Auth::user();

            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $total += $product->price * $item['quantity'];
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $total,
                'status' => 'pending'
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            return response()->json($order->load('items.product'), 201);
        });
    }



    public function show(Order $order)
    {
        $this->authorizeAccess($order);
        return response()->json($order->load('items'));
    }

    public function update(Request $request, Order $order)
    {
        $user = Auth::user();

        if ($user->hasRole('delivery')) {
            if ($order->delivery_id === null) {
                $order->update([
                    'delivery_id' => $user->id,
                    'delivery_name' => $user->name,
                    'status' => 'confirmed'
                ]);
            } elseif ($order->delivery_id === $user->id) {
                $order->update(['status' => 'delivered']);
            } else {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } elseif ($user->hasRole('admin')) {
            $request->validate([
                'status' => 'required|string|in:pending,confirmed,delivered,cancelled'
            ]);
            $order->update($request->only('status'));
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order->load('items'));
    }

    public function destroy(Order $order)
    {
        $this->authorizeRole('admin');
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully.']);
    }

    public function takeOrder(Order $order)
    {
        $user = Auth::user();

        if (!$user->hasRole('delivery')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order already taken'], 400);
        }

        $order->update([
            'delivery_id' => $user->id,
            'delivery_name' => $user->name,
            'status' => 'confirmed',
        ]);

        return response()->json(['message' => 'Order taken successfully', 'order' => $order]);
    }


    private function authorizeAccess($order)
    {
        $user = Auth::user();
        if (
            $user->hasRole('admin') ||
            ($user->hasRole('customer') && $order->user_id == $user->id) ||
            ($user->hasRole('delivery') && $order->delivery_id == $user->id)
        ) {
            return true;
        }

        abort(403, 'Unauthorized');
    }

    private function authorizeRole($role)
    {
        if (!Auth::user()->hasRole($role)) {
            abort(403, 'Unauthorized');
        }
    }


public function createCheckoutSession(Request $request){
    try {
        // Get authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Validate request
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $lineItems = [];
        $total = 0;

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            
            // Check stock
            if ($item['quantity'] > $product->stock) {
                return response()->json([
                    'error' => "Not enough stock for product: {$product->name}"
                ], 400);
            }

            $price = $product->price * 100; // in cents
            $total += $product->price * $item['quantity'];

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $product->name,
                    ],
                    'unit_amount' => intval($price),
                ],
                'quantity' => $item['quantity'],
            ];
        }

        // Set Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        //Create Stripe checkout session
        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => env('APP_FRONTEND_URL') . '/checkout-success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => env('APP_FRONTEND_URL') . '/payment-cancelled','metadata' => [
                'user_id' => $user->id,
                'items' => json_encode($validated['items']),
            ]
        ]);

        return response()->json(['url' => $session->url]);

    } catch (\Illuminate\Validation\ValidationException $ve) {
        // Return validation errors
        return response()->json(['errors' => $ve->errors()], 422);
    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Checkout Error: ' . $e->getMessage(), [
            'stack' => $e->getTraceAsString(),
            'request' => $request->all(),
        ]);

        return response()->json([
            'error' => 'An error occurred while creating checkout session',
            'message' => $e->getMessage()
        ], 500);
    }
}


public function paymentSuccess(Request $request) {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = \Stripe\Checkout\Session::retrieve($request->session_id);

        if ($session->payment_status !== 'paid') {
            return response()->json(['error' => 'Payment not completed'], 400);
        }

        $user = User::find($session->metadata->user_id);
        $items = json_decode($session->metadata->items, true);

       $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $session->amount_total / 100,
                'status' => 'pending',
                'type' => 'vente',  // <-- add this
            ]);

        // Create order items
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);

            // Reduce stock
            $product->decrement('stock', $item['quantity']);
        }

        // Create payment record
        $order->payment()->create([
            'payment_method' => 'stripe',
            'payment_status' => 'paid',
            'stripe_session_id' => $session->id,
            'stripe_payment_intent_id' => $session->payment_intent,
        ]);

        return response()->json([
            'message' => 'Order created successfully after payment!',
            'order' => $order->load('items', 'payment') // optional: eager load for frontend
        ]);
    }
}
