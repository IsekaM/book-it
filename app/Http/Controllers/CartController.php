<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    /**
     * Adds a book to the user's cart
     *
     * @param  Request  $request
     * @param  Book  $book
     * @return JsonResponse
     */
    public function add(Request $request, Book $book)
    {
        $cart = Cart::firstOrCreate(["user_id" => $request->user()->id]);

        $cart->books()->sync($book->id);

        $cart->load("books:id,author,title,price,isbn");

        return response()->formattedJson($cart);
    }

    /**
     * Removes a book from the user's cart
     *
     * @param  Request  $request
     * @param  Book  $book
     * @return JsonResponse
     */
    public function remove(Request $request, Book $book)
    {
        $cart = Cart::firstOrCreate(["user_id" => $request->user()->id]);

        $cart->books()->detach($book->id);

        $cart->load("books:id,author,title,price,isbn");

        return response()->formattedJson($cart);
    }
}
