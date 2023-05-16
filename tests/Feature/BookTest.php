<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Book;
use Illuminate\Testing\TestResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Book|Book[]|Collection|Model|\LaravelIdea\Helper\App\Models\_IH_Book_C
     */
    private array|Book|Collection|Model $books;

    protected function setUp(): void
    {
        parent::setUp();

        $this->books = Book::factory()
            ->count(20)
            ->create();
    }

    private function fetchBooks(?string $query = null): TestResponse
    {
        return $this->getJson(route("api.books.index", ["q" => $query]));
    }

    public function testBooksCanBeFetched()
    {
        $this->fetchBooks()
            ->assertOk()
            ->assertJsonStructure([
                "success",
                "data" => [
                    "*" => [
                        "id",
                        "author",
                        "isbn",
                        "price",
                        "created_at",
                        "updated_at",
                    ],
                ],
                "current_page",
                "first_page_url",
                "last_page_url",
                "links",
            ]);
    }

    public function testUserCanSearchForBooks()
    {
        $randomBook = $this->books->first();

        $this->fetchBooks($randomBook->title)
            ->assertOk()
            ->assertJsonFragment([
                "title" => $randomBook->title,
            ]);

        $this->fetchBooks($randomBook->isbn)
            ->assertOk()
            ->assertJsonFragment(["isbn" => $randomBook->isbn]);

        $this->fetchBooks($randomBook->author)
            ->assertOk()
            ->assertJsonFragment(["author" => $randomBook->author]);
    }
}
