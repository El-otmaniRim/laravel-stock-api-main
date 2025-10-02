<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;

class AdminUserRoleController extends Controller
{

    public function assignDeliveryRole($userId){
        $user = User::findOrFail($userId);

        if (!$user->hasRole('delivery')) {
            $user->assignRole('delivery');
        }

        return response()->json([
            'message' => 'Delivery role assigned to user.',
            'user' => $user->load('roles'),
        ]);
    }

    // In your AdminUserRoleController or OrdersController

    public function assignDeliveryToOrder(Request $request, $orderId){
        $request->validate([
            'livreurId' => 'required|exists:users,id',
        ]);

        $order = Order::findOrFail($orderId);

        // Assign delivery person to order
        $order->delivery_id = $request->livreurId;
        $order->save();

        return response()->json([
            'message' => 'Delivery person assigned successfully.',
            'order' => $order->load('livreur') // assuming you have a relation defined
        ]);
    }


    public function removeDeliveryRole($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->hasRole('delivery')) {
            $user->removeRole('delivery');
        }

        return response()->json([
            'message' => 'Delivery role removed from user.',
            'user' => $user->load('roles'),
        ]);
    }

    public function deliveryUsers(){
        $deliveryUsers = User::whereHas('roles', function ($query) {
                $query->where('name', 'delivery');
        })->with('roles')->get();

        return response()->json([
            'users' => $deliveryUsers
        ]);
    }


    public function allUsers()
    {
        $users = User::with('roles')->get();

        return response()->json([
            'users' => $users
        ]);
    }

     public function allOrders()
{
    $orders = Order::with([
    'items.product:id,name,description',
    'payment:id,order_id,payment_method,payment_status,created_at',
    'user:id,name',         // eager load client
    ])->get();

    return OrderResource::collection($orders)->resolve();

}

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->hasRole('admin')) {
            return response()->json(['message' => 'Cannot delete an admin user.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function deleteOrder($id){

        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully.']);
    }


    public function updateOrderStatus(Request $request, $id){

        $request->validate([
            'status' => 'required|string|in:pending,processing,confirmed,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);

        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Order status updated successfully.',
            'order' => new OrderResource($order)
        ]);
    }
}
