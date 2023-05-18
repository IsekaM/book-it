<?php

namespace App\DataTransferObjects;

use ReflectionProperty;
use ReflectionException;
use Illuminate\Support\Str;

abstract class DataTransferObject
{
    public static function fromArray(array $data): static
    {
        $instance = new static();

        self::setProperties($data, $instance);

        self::setUninitializedPropertiesToNull($instance);

        return $instance;
    }

    private static function setProperties(array $data, self $instance): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->{$key} = $value;

                continue;
            }

            if (property_exists($instance, Str::camel($key))) {
                $instance->{Str::camel($key)} = $value;
            }
        }
    }

    private static function setUninitializedPropertiesToNull(
        self $instance,
    ): void {
        $properties = self::reflectProperties($instance);

        foreach ($properties as $property) {
            if (
                !$property->isInitialized($instance) &&
                $property->getType()->allowsNull()
            ) {
                $instance->{$property->getName()} = null;
            }
        }
    }

    /**
     * @param  DataTransferObject|string  $instance
     * @return ReflectionProperty[]
     *
     * @throws ReflectionException
     */
    private static function reflectProperties(self|string $instance): array
    {
        return (new \ReflectionClass($instance))->getProperties();
    }
}
