<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\BookRequest;
use Illuminate\Database\Eloquent\Builder;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $request->query("q");

        $books = Book::when($query, function (Builder $q) use ($query) {
            return $q
                ->where("author", "like", "%$query%")
                ->orWhere("isbn", "like", "%$query%")
                ->orWhere("title", "like", "%$query%");
        })->paginate(Controller::PAGINATION_AMOUNT);

        return response()->formattedJson($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookRequest $request)
    {
        $this->authorize("create", Book::class);

        $book = Book::create($request->validated());

        return response()->formattedJson($book);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        return response()->formattedJson($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookRequest $request, Book $book)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $this->authorize("delete", $book);

        $book->delete();

        return response()->formattedJson(null, Response::HTTP_NO_CONTENT);
    }
}
