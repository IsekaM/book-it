<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\BookRequest;
use Illuminate\Database\Eloquent\Builder;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:sanctum")->except(["index", "show"]);
    }

    /**
     * @lrd:start
     * # Get All Books
     * This endpoint gets all books as a paginated list
     * @lrd:end
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
     * @lrd:start
     * # Get All Books
     * Stores a newly created book. This endpoint is
     * accessible by admins only
     * @lrd:end
     */
    public function store(BookRequest $request)
    {
        $this->authorize("create", Book::class);

        $book = Book::create($request->validated());

        return response()->formattedJson($book);
    }

    /**
     * @lrd:start
     * # Show A Single Book
     * Shows a specified book
     * @lrd:end
     */
    public function show(Book $book)
    {
        return response()->formattedJson($book);
    }

    /**
     * @lrd:start
     * # Update A Single Book
     * Updates a specified book. This is accessible by admins only
     * @lrd:end
     */
    public function update(BookRequest $request, Book $book)
    {
        $this->authorize("update", $book);

        $book->update($request->validated());

        return response()->formattedJson($book, Response::HTTP_CREATED);
    }

    /**
     * @lrd:start
     * # Deletes a single book
     * Deletes a specified book from database
     * @lrd:end
     */
    public function destroy(Book $book)
    {
        $this->authorize("delete", Book::class);

        $book->delete();

        return response()->formattedJson(null, Response::HTTP_NO_CONTENT);
    }
}
