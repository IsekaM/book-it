<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Book;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\TestResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Book|Book[]|Collection|Model
     */
    private array|Book|Collection|Model $books;

    private Model $adminUser;

    private Book|Model $singleBook;

    private User|Model $memberUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->books = Book::factory()
            ->count(20)
            ->create();

        $this->singleBook = Book::factory()->createOne();

        $this->adminUser = User::factory()
            ->admin()
            ->createOne();

        $this->memberUser = User::factory()->createOne();
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

    public function testAdminCanDeleteABook()
    {
        Sanctum::actingAs($this->adminUser, ["*"]);

        $this->deleteJson(
            route("api.books.destroy", ["book" => $this->singleBook]),
        )->assertNoContent();

        $this->assertDatabaseMissing(Book::class, [
            "id" => $this->singleBook->id,
        ]);
    }

    public function testMemberCantDeleteABook()
    {
        Sanctum::actingAs($this->memberUser, ["*"]);

        $this->deleteJson(
            route("api.books.destroy", ["book" => $this->singleBook]),
        )->assertForbidden();

        $this->assertDatabaseHas(Book::class, ["id" => $this->singleBook->id]);
    }
}
