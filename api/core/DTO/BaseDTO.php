<?php
namespace Core\DTO;

abstract class BaseDTO
{
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $propertyType = $this->getPropertyType($key);

            if ($this->isDtoClass($propertyType) && is_array($value)) {
                $this->$key = $propertyType::fromArray($value);
                continue;
            }

            if ($propertyType === 'array' && is_array($value)) {
                $dtoClass = $this->getArrayItemDtoClass($key);
                

                if (!is_string($dtoClass) || !class_exists($dtoClass) || !is_subclass_of($dtoClass, self::class)) {
                    continue;
                }

                /** @var class-string<BaseDTO> $dtoClass */
                $cls = $dtoClass;

                $this->$key = array_map(
                    function ($item) use ($cls) {
                        return $cls::fromArray($item);
                    },
                    $value
                );

                continue;
            }

            $this->$key = $value;
        }
    }

    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    public function toArray(): array
    {
        $result = [];
        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof BaseDTO) {
                $result[$key] = $value->toArray();
            } elseif (is_array($value) && $this->isArrayOfDtosByValue($value)) {
                $result[$key] = array_map(fn($item) => $item->toArray(), $value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    // --- Métodos auxiliares ---

    private function getPropertyType(string $property): ?string
    {
        $reflection = new \ReflectionProperty($this, $property);
        $type = $reflection->getType();
        if ($type instanceof \ReflectionNamedType) {
            if ($type->isBuiltin()) {
                return $type->getName(); // Retornará 'array', 'string', etc.
            }
            return $type->getName(); // Retornará o nome da classe
        }
        return null;
    }

    private function isDtoClass(?string $className): bool
    {
        if (!$className || !class_exists($className)) return false;
        return is_subclass_of($className, self::class);
    }

    private function isArrayOfDtos(string $property): bool
    {
        $reflection = new \ReflectionProperty($this, $property);
        $docComment = $reflection->getDocComment();
        if (!$docComment) return false;
        // Procura por @var Tipo[]
        return preg_match('/@var\s+([A-Za-z0-9_\\\]+)\[\]/', $docComment) === 1;
    }

    private function getArrayItemDtoClass(string $property): ?string
    {
        $reflection = new \ReflectionProperty($this, $property);
        $docComment = $reflection->getDocComment();
        if (!$docComment) return null;

        // Suporte a namespaces completos no PHPDoc
        if (preg_match('/@var\s+([A-Za-z0-9_\\\]+)\[\]/', $docComment, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function isArrayOfDtosByValue(array $value): bool
    {
        if (empty($value)) return false;
        $first = reset($value);
        return $first instanceof BaseDTO;
    }
}