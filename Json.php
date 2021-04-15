<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Feast;

use Feast\Attributes\JsonItem;
use Feast\Collection\Collection;
use Feast\Collection\CollectionList;
use Feast\Collection\Set;
use Feast\Exception\InvalidArgumentException;
use Feast\Interfaces\JsonSerializableInterface;
use ReflectionException;
use ReflectionProperty;

class Json
{

    /**
     * Marshal an object into a JsonString.
     *
     * The field names are kept as is, unless a Feast\Attributes\JsonItem attribute decorates the property.
     *
     * @param JsonSerializableInterface $object
     * @return string
     * @throws ReflectionException
     */
    public static function marshal(JsonSerializableInterface $object): string
    {
        $return = new \stdClass();
        $paramInfo = self::getClassParamInfo($object::class);
        foreach ($paramInfo as $oldName => $newInfo) {
            $newName = $newInfo['name'];

            $reflected = new ReflectionProperty($object, $oldName);
            if ($reflected->isInitialized($object)) {
                if ($object->{$oldName} instanceof JsonSerializableInterface) {
                    $return->{$newName} = json_decode(self::marshal($object->{$oldName}));
                } elseif (is_array($object->{$oldName})) {
                    $return->{$newName} = self::marshalArray($object->{$oldName});
                } elseif ($object->{$oldName} instanceof Collection) {
                    $return->{$newName} = self::marshalArray(($object->{$oldName})->toArray());
                } else {
                    $return->{$newName} = $object->{$oldName};
                }
            }
        }
        return json_encode($return);
    }

    /**
     * Unmarshal a JSON string into a class that implements the JsonSerializableInterface.
     *
     * Property types can be decorated with the Feast\Attributes\JsonItem attribute. This type info allows layered marshalling.
     *
     * @param string $data
     * @param class-string $className
     * @return JsonSerializableInterface
     * @throws ReflectionException|Exception\ServerFailureException
     */
    public static function unmarshal(string $data, string $className): JsonSerializableInterface
    {
        if (is_a($className, JsonSerializableInterface::class, true) === false) {
            throw new InvalidArgumentException($className . ' must implement JsonSerializableInterface');
        }
        $jsonData = json_decode($data, true, flags: JSON_THROW_ON_ERROR);
        $paramInfo = self::getClassParamInfo($className);
        $object = new $className();
        $classInfo = new \ReflectionClass($className);
        foreach ($classInfo->getProperties() as $property) {
            $propertyName = $paramInfo[$property->getName()]['name'] ?? $property->getName();
            if (!array_key_exists($propertyName, $jsonData)) {
                continue;
            }
            $propertyType = (string)$property->getType();
            $propertySubtype = (string)$paramInfo[$property->getName()]['type'];
            if ($propertyType === 'array') {
                self::unmarshalArray(
                    $property,
                    $object,
                    $propertySubtype,
                    $jsonData[$propertyName],

                );
            } elseif (is_a($propertyType, Set::class, true)) {
                self::unmarshalSet(
                    $propertySubtype,
                    $jsonData[$propertyName],
                    $property,
                    $object
                );
            } elseif (is_a($propertyType, Collection::class, true)) {
                self::unmarshalCollection($propertySubtype, $jsonData[$propertyName], $property, $object);
            } elseif (is_a($propertyType, JsonSerializableInterface::class, true)) {
                $object->{$property->getName()} = self::unmarshal(
                    json_encode($jsonData[$propertyName]),
                    $propertyType
                );
            } else {
                $object->{$property->getName()} = $jsonData[$propertyName];
            }
        }

        return $object;
    }

    /**
     * @param array $items
     * @return array
     * @throws ReflectionException
     */
    protected static function marshalArray(
        array $items
    ): array {
        $return = [];
        foreach ($items as $key => $item) {
            if ($item instanceof JsonSerializableInterface) {
                $return[$key] = json_decode(self::marshal($item));
            } elseif (is_array($item)) {
                $return[$key] = self::marshalArray($item);
            } else {
                $return[$key] = $item;
            }
        }

        return $return;
    }

    /**
     * @param class-string $class
     * @return array
     * @throws ReflectionException
     */
    protected static function getClassParamInfo(
        string $class
    ): array {
        $return = [];
        $classInfo = new \ReflectionClass($class);
        foreach ($classInfo->getProperties() as $property) {
            $name = $property->getName();
            $type = null;
            $attributes = $property->getAttributes(JsonItem::class);
            foreach ($attributes as $attribute) {
                /** @var JsonItem $attributeObject */
                $attributeObject = $attribute->newInstance();
                $name = $attributeObject->name ?? $name;
                $type = $attributeObject->arrayOrCollectionType;
            }
            $return[$property->getName()] = ['name' => $name, 'type' => $type];
        }
        return $return;
    }

    /**
     * @param ReflectionProperty $property
     * @param JsonSerializableInterface $object
     * @param string $propertySubtype
     * @param array $jsonData
     * @throws Exception\ServerFailureException
     * @throws ReflectionException
     */
    protected static function unmarshalArray(
        ReflectionProperty $property,
        JsonSerializableInterface $object,
        string $propertySubtype,
        array $jsonData
    ): void {
        $object->{$property->getName()} = [];
        if (is_a($propertySubtype, JsonSerializableInterface::class, true)) {
            foreach ($jsonData as $key => $val) {
                $object->{$property->getName()}[$key] = self::unmarshal(
                    json_encode($val),
                    $propertySubtype
                );
            }
        } else {
            $object->{$property->getName()} = $jsonData;
        }
    }

    /**
     * @param string $propertySubtype
     * @param array $jsonData
     * @param ReflectionProperty $property
     * @param JsonSerializableInterface $object
     * @throws Exception\ServerFailureException|ReflectionException
     */
    protected static function unmarshalSet(
        string $propertySubtype,
        array $jsonData,
        ReflectionProperty $property,
        JsonSerializableInterface $object
    ): void {
        if (is_a($propertySubtype, JsonSerializableInterface::class, true)) {
            $jsonData = self::unmarshalTempArray($jsonData, $propertySubtype);
        }
        $object->{$property->getName()} = new Set(
            $propertySubtype,
            $jsonData,
            preValidated: true
        );
    }

    /**
     * @param string $propertySubtype
     * @param array $jsonData
     * @param ReflectionProperty $property
     * @param JsonSerializableInterface $object
     * @throws Exception\ServerFailureException|ReflectionException
     */
    protected static function unmarshalCollection(
        string $propertySubtype,
        array $jsonData,
        ReflectionProperty $property,
        JsonSerializableInterface $object
    ): void {
        if (is_a($propertySubtype, JsonSerializableInterface::class, true)) {
            $jsonData = self::unmarshalTempArray($jsonData, $propertySubtype);
        }
        $object->{$property->getName()} = new CollectionList(
            $propertySubtype,
            $jsonData,
            preValidated: true
        );
    }

    /**
     * @param array $jsonData
     * @param string $propertySubtype
     * @return array
     * @throws Exception\ServerFailureException
     * @throws ReflectionException
     */
    protected static function unmarshalTempArray(array $jsonData, string $propertySubtype): array
    {
        $tempArray = [];
        foreach ($jsonData as $key => $val) {
            $tempArray[$key] = self::unmarshal(
                json_encode($val),
                $propertySubtype
            );
        }
        return $tempArray;
    }
}
