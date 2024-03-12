<?php

abstract class ShapeType implements ArrayAccess {
  public function __construct(stdClass|array $array = []) {
    assert(is_array($array) || $array instanceof stdClass);
    $shape = (array) $array;

    $reflection = new ReflectionClass($this);
    $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

    // Throw an error when passing more properties than expected
    $shapeKeys = array_keys($shape);
    $propertyNames = array_column($properties, 'name');
    $extraKeys = array_diff($shapeKeys, $propertyNames);

    if (count($extraKeys) > 0) {
      throw new Exception("Extra properties found: " . implode(', ', $extraKeys));
    }

    foreach ($properties as $property) {
      assert($property instanceof ReflectionProperty);

      $name = $property->getName();
      $type = $property->getType();
      assert($type instanceof ReflectionType);

      if (!array_key_exists($name, $shape)) {
        if ($type->allowsNull()) {
          $property->setValue($this, null);
        } else {
          throw new Exception("Property not found: {$name}");
        }
      } else {
        $value = $shape[$name];

        if (is_scalar($value) || is_null($value)) {
          $property->setValue($this, $value);
        } else if (is_array($value)) {
          if ($type instanceof ReflectionNamedType) {
            if ($type->getName() === 'array') {
              $property->setValue($this, $value);
            } else {
              $property->setValue($this, new ($type->getName())($value));
            }
          } else {
            throw new Exception("Array expected, got: " . get_class($type));
          }
        } else {
          throw new Exception("Unsupported type: " . gettype($value));
        }
      }
    }
  }

  // ArrayAccess::offsetExists
  public function offsetExists(mixed $offset): bool {
    return property_exists($this, $offset);
  }

  // ArrayAccess::offsetGet
  public function offsetGet(mixed $offset): mixed {
    return $this->{$offset};
  }

  // ArrayAccess::offsetSet
  public function offsetSet(mixed $offset, mixed $value): void {
    $this->{$offset} = $value;
  }

  // ArrayAccess::offsetUnset
  public function offsetUnset(mixed $offset): void {
    unset($this->{$offset});
  }
}
