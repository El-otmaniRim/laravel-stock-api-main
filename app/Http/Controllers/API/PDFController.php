<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;

class PDFController extends Controller
{
    // public function users()
    // {
    //     $users = User::all();
    //     $pdf = PDF::loadView('pdf.users', compact('users'));
    //     return $pdf->download('users.pdf');
    // }


    public function users()
{
    $users = User::all();

    $html = '<h1>Liste des utilisateurs</h1><table width="100%" border="1" cellspacing="0" cellpadding="5"><thead><tr><th>ID</th><th>Nom</th><th>Email</th></tr></thead><tbody>';

    foreach ($users as $user) {
        $html .= '<tr>';
        $html .= '<td>' . $user->id . '</td>';
        $html .= '<td>' . $user->name . '</td>';
        $html .= '<td>' . $user->email . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    $pdf = Pdf::loadHTML($html);
    return $pdf->download('users.pdf');
}

    public function orders()
    {
        $orders = Order::with('items.product', 'user')->get();
        $pdf = PDF::loadView('pdf.orders', compact('orders'));
        return $pdf->download('orders.pdf');
    }

    public function products()
    {
        $products = Product::all();
        $pdf = PDF::loadView('pdf.products', compact('products'));
        return $pdf->download('products.pdf');
    }

    public function deliveryOrders()
    {
        $user = Auth::user();
        $orders = Order::where('delivery_id', $user->id)->with('items.product')->get();
        $pdf = PDF::loadView('pdf.delivery_orders', compact('orders'));
        return $pdf->download('delivery_orders.pdf');
    }

    public function myOrders()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->with('items.product')->get();
        $pdf = PDF::loadView('pdf.my_orders', compact('orders'));
        return $pdf->download('my_orders.pdf');
    }
}
