<?php

namespace Tests\Unit;

use ReflectionProperty;
use PHPUnit\Framework\TestCase;
use App\DataTransferObjects\DataTransferObject;

class DataTransferObjectTest extends TestCase
{
    public function test_data_transfer_object_can_be_made_from_array()
    {
        $dtoClass = new class extends DataTransferObject {
            public string $propertyOne;

            public string $propertyTwo;
        };

        $dto = $dtoClass::fromArray([
            "propertyOne" => "foo",
            "propertyTwo" => "bar",
        ]);

        $this->assertEquals("foo", $dto->propertyOne);

        $this->assertEquals("bar", $dto->propertyTwo);
    }

    public function test_data_transfer_object_can_be_made_from_array_where_keys_arent_camel_case()
    {
        $dtoClass = new class extends DataTransferObject {
            public string $propertyOne;

            public string $propertyTwo;
        };

        $dto = $dtoClass::fromArray([
            "property_one" => "foo",
            "property_two" => "bar",
        ]);

        $this->assertEquals("foo", $dto->propertyOne);

        $this->assertEquals("bar", $dto->propertyTwo);
    }

    public function test_properties_will_not_be_assigned_if_keys_dont_match_the_properties()
    {
        $dtoClass = new class extends DataTransferObject {
            public string $propertyOne;

            public string $propertyTwo;
        };

        $dto = $dtoClass::fromArray([
            "property1" => "foo",
            "property2" => "bar",
        ]);

        $this->assertFalse(
            (new ReflectionProperty($dto, "propertyOne"))->isInitialized($dto),
        );

        $this->assertFalse(
            (new ReflectionProperty($dto, "propertyTwo"))->isInitialized($dto),
        );

        $this->assertObjectNotHasProperty("property1", $dto);
        $this->assertObjectNotHasProperty("property2", $dto);
    }

    public function test_properties_are_set_to_null_if_a_value_is_not_passed_in()
    {
        $dtoClass = new class extends DataTransferObject {
            public ?string $propertyOne;

            public ?string $propertyTwo;
        };

        $dto = $dtoClass::fromArray(["propertyTwo" => "bar"]);

        $this->assertEquals("bar", $dto->propertyTwo);
        $this->assertNull($dto->propertyOne);
    }

    public function test_properties_cant_be_automatically_set_if_they_dont_allow_it_and_value_is_not_set()
    {
        $dtoClass = new class extends DataTransferObject {
            public string $propertyOne;

            public ?string $propertyTwo;
        };

        $dto = $dtoClass::fromArray(["propertyTwo" => "bar"]);

        $this->assertEquals("bar", $dto->propertyTwo);
        $this->assertTrue(
            !(new ReflectionProperty($dto, "propertyOne"))->isInitialized($dto),
        );
    }
}
