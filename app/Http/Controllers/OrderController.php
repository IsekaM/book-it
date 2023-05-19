<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @lrd:start
     * # List User's Orders
     * Fetches all orders a user has made along with the
     * books in that order.
     * @lrd:end
     */
    public function index(Request $request)
    {
        return response()->formattedJson(
            Order::with("books:id,title,author,price,isbn")
                ->where("user_id", $request->user())
                ->paginate(Controller::PAGINATION_AMOUNT),
        );
    }
}
