<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Book;
use App\Models\User;
use App\Models\Cart;
use App\Models\BookCart;
use Laravel\Sanctum\Sanctum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private Book|Model $book;

    private Model $memberUser;

    private Cart|Model $cart;

    protected function setUp(): void
    {
        parent::setUp();

        $this->book = Book::factory()->createOne();

        $this->memberUser = User::factory()
            ->member()
            ->createOne();

        $this->cart = Cart::factory()
            ->has(Book::factory()->count(3))
            ->for($this->memberUser)
            ->createOne();
    }

    public function testItemCanBeAddedToCart()
    {
        Sanctum::actingAs($this->memberUser, ["*"]);

        $this->postJson(route("api.cart.add", $this->book->id))
            ->assertOk()
            ->assertJsonStructure([
                "success",
                "data" => [
                    "id",
                    "user_id",
                    "created_at",
                    "updated_at",
                    "books" => [
                        "*" => ["id", "author", "price", "title", "isbn"],
                    ],
                ],
            ]);

        $this->assertDatabaseCount(BookCart::class, 1);
    }

    public function testItemCanBeRemovedFromCart()
    {
        Sanctum::actingAs($this->memberUser, ["*"]);

        $this->postJson(
            route("api.cart.remove", $this->cart->books->first()->id),
        )
            ->assertOk()
            ->assertJsonStructure([
                "success",
                "data" => [
                    "id",
                    "user_id",
                    "created_at",
                    "updated_at",
                    "books" => [],
                ],
            ]);

        $this->assertDatabaseCount(BookCart::class, 2);
    }
}
