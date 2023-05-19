<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    /**
     * @lrd:start
     * # List User's Orders
     * Gets all books stored in the database as a paginated list.
     * If the user sending the request is a member, it only shows the
     * orders they made. If the user making the request is an admin, order's
     * for all user's will be shown.
     * @lrd:end
     */
    public function index(Request $request)
    {
        $order = Order::query()
            ->with("books:id,title,author,price,isbn")
            ->latest();
        $user = $request->user();

        if ($user->isMember()) {
            $order->where("user_id", $user->id);
        }

        return response()->formattedJson(
            $order->paginate(Controller::PAGINATION_AMOUNT),
        );
    }

    /**
     * @lrd:start
     * # List User's Orders
     * Fetches all orders a user has made along with the books in those
     * orders. Only admins can access this endpoint
     * @lrd:end
     */
    public function indexUser(User $user)
    {
        $this->authorize("viewAllForUser", Order::class);

        return response()->formattedJson(
            Order::with("books:id,title,author,price,isbn")
                ->where("user_id", $user->id)
                ->latest()
                ->paginate(Controller::PAGINATION_AMOUNT),
        );
    }

    /**
     * @lrd:start
     * # Show a Specified Order
     * Shows the order based on the ID provided to the endpoint
     * @lrd:end
     */
    public function show(Order $order)
    {
        $this->authorize("view", $order);

        return response()->formattedJson(
            $order->load("books:id,title,author,price,isbn"),
        );
    }

    /**
     * @lrd:start
     * # Delete User's Order
     * Removes a user's order from the database. This endpoint
     * can only be used by admin users
     * @lrd:end
     */
    public function destroy(Order $order)
    {
        $this->authorize("delete", Order::class);

        $order->delete();

        return response()->formattedJson(null, Response::HTTP_NO_CONTENT);
    }
}
