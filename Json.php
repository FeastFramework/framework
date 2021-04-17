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
use Feast\Exception\ServerFailureException;
use ReflectionException;
use ReflectionProperty;

class Json
{

    /**
     * Marshal an object into a JsonString.
     *
     * The field names are kept as is, unless a Feast\Attributes\JsonItem attribute decorates the property.
     *
     * @param object $object
     * @return string
     * @throws ReflectionException
     * @see \Feast\Attributes\JsonItem
     */
    public static function marshal(object $object): string
    {
        $return = new \stdClass();
        $paramInfo = self::getClassParamInfo($object::class);
        /**
         * @var string $oldName
         * @var array{name:string|null,type:string|null,dateFormat:string} $newInfo
         */
        foreach ($paramInfo as $oldName => $newInfo) {
            $newName = $newInfo['name'];

            $reflected = new ReflectionProperty($object, $oldName);
            if ($reflected->isInitialized($object)) {
                /** @var scalar|object|array $oldItem */
                $oldItem = $object->{$oldName};
                if (is_object(
                        $oldItem
                    ) && $oldItem instanceof Collection === false && $oldItem instanceof Date === false) {
                    $return->{$newName} = (object)json_decode(self::marshal($oldItem));
                } elseif (is_array($oldItem)) {
                    $return->{$newName} = self::marshalArray($oldItem);
                } elseif ($oldItem instanceof Collection) {
                    $return->{$newName} = self::marshalArray($oldItem->toArray());
                } elseif ($oldItem instanceof Date) {
                    $return->{$newName} = $oldItem->getFormattedDate($newInfo['dateFormat']);
                } else {
                    $return->{$newName} = $oldItem;
                }
            }
        }
        return json_encode($return);
    }

    /**
     * Unmarshal a JSON string into a class.
     *
     * Property types can be decorated with the Feast\Attributes\JsonItem attribute.
     * This type info allows layered marshalling.
     *
     * @param string $data
     * @param class-string|object $objectOrClass
     * @return object
     * @throws Exception\ServerFailureException
     * @throws ReflectionException
     * @see \Feast\Attributes\JsonItem
     */
    public static function unmarshal(string $data, string|object $objectOrClass): object
    {
        if (is_string($objectOrClass)) {
            try {
                /** @psalm-suppress MixedMethodCall */
                $object = new $objectOrClass();
            } catch (\ArgumentCountError) {
                throw new ServerFailureException(
                    'Attempted to unmarshal into a class without a no-argument capable constructor'
                );
            }
        } else {
            $object = $objectOrClass;
        }
        $className = $object::class;
        /** @var array $jsonData */
        $jsonData = json_decode($data, true, flags: JSON_THROW_ON_ERROR);
        $paramInfo = self::getClassParamInfo($className);

        $classInfo = new \ReflectionClass($className);
        foreach ($classInfo->getProperties() as $property) {
            $newPropertyName = $property->getName();
            /** @var string $propertyName */
            $propertyName = $paramInfo[$newPropertyName]['name'] ?? $newPropertyName;
            if (!array_key_exists($propertyName, $jsonData)) {
                continue;
            }
            /** @var scalar|array $propertyValue */
            $propertyValue = $jsonData[$propertyName];
            self::unmarshalProperty(
                $property,
                (string)$paramInfo[$newPropertyName]['type'],
                (string)$paramInfo[$newPropertyName]['dateFormat'],
                $propertyValue,
                $object
            );
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
        /**
         * @var string $key
         * @var scalar|object|array $item
         */
        foreach ($items as $key => $item) {
            if (is_object($item)) {
                $return[$key] = (array)json_decode(self::marshal($item));
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
     * @return array<array{name:string|null,type:string|null,dateFormat:string|null}>
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
            $dateFormat = Date::ISO8601;
            $attributes = $property->getAttributes(JsonItem::class);
            foreach ($attributes as $attribute) {
                /** @var JsonItem $attributeObject */
                $attributeObject = $attribute->newInstance();
                $name = $attributeObject->name ?? $name;
                $type = $attributeObject->arrayOrCollectionType;
                $dateFormat = $attributeObject->dateFormat;
            }
            $return[$property->getName()] = ['name' => $name, 'type' => $type, 'dateFormat' => $dateFormat];
        }
        return $return;
    }

    /**
     * @param ReflectionProperty $property
     * @param object $object
     * @param string $propertySubtype
     * @param array $jsonData
     * @throws Exception\ServerFailureException
     * @throws ReflectionException
     */
    protected static function unmarshalArray(
        ReflectionProperty $property,
        object $object,
        string $propertySubtype,
        array $jsonData
    ): void {
        $newProperty = $property->getName();
        $object->{$newProperty} = [];
        if (class_exists($propertySubtype, true)) {
            /**
             * @var string $key
             * @var scalar|object|array $val
             */
            foreach ($jsonData as $key => $val) {
                $object->{$newProperty}[$key] = self::unmarshal(
                    json_encode($val),
                    $propertySubtype
                );
            }
        } else {
            $object->{$newProperty} = $jsonData;
        }
    }

    /**
     * @param string $propertySubtype
     * @param array<string|int|bool|float|object|array> $jsonData
     * @param ReflectionProperty $property
     * @param object $object
     * @throws Exception\ServerFailureException|ReflectionException
     */
    protected static function unmarshalSet(
        string $propertySubtype,
        array $jsonData,
        ReflectionProperty $property,
        object $object
    ): void {
        if (class_exists($propertySubtype, true)) {
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
     * @param array<array> $jsonData
     * @param ReflectionProperty $property
     * @param object $object
     * @throws Exception\ServerFailureException|ReflectionException
     */
    protected static function unmarshalCollection(
        string $propertySubtype,
        array $jsonData,
        ReflectionProperty $property,
        object $object
    ): void {
        if (class_exists($propertySubtype, true)) {
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
     * @return array<string|int|bool|float|object|array>
     * @throws Exception\ServerFailureException
     * @throws ReflectionException
     * @noinspection PhpDocSignatureInspection
     */
    protected static function unmarshalTempArray(array $jsonData, string $propertySubtype): array
    {
        $tempArray = [];
        /**
         * @var string $key
         * @var string|int|bool|float|object|array $val
         */
        foreach ($jsonData as $key => $val) {
            /** @psalm-suppress ArgumentTypeCoercion */
            $tempArray[$key] = self::unmarshal(
                json_encode($val),
                $propertySubtype
            );
        }
        return $tempArray;
    }

    /**
     * Unmarshal a property onto the object.
     *
     * @param ReflectionProperty $property
     * @param class-string|string $propertySubtype
     * @param string $propertyDateFormat
     * @param scalar|array $propertyValue
     * @param object $object
     * @throws Exception\InvalidDateException
     * @throws ReflectionException
     * @throws ServerFailureException
     */
    protected static function unmarshalProperty(
        ReflectionProperty $property,
        string $propertySubtype,
        string $propertyDateFormat,
        mixed $propertyValue,
        object $object
    ): void {
        $propertyType = (string)$property->getType();

        if ($propertyType === 'array' && is_array($propertyValue)) {
            self::unmarshalArray(
                $property,
                $object,
                $propertySubtype,
                $propertyValue,
            );
        } elseif (is_a($propertyType, Set::class, true) && is_array($propertyValue)) {
            /** @psalm-suppress MixedArgumentTypeCoercion */
            self::unmarshalSet(
                $propertySubtype,
                $propertyValue,
                $property,
                $object
            );
        } elseif (is_a($propertyType, Collection::class, true) && is_array($propertyValue)) {
            /** @psalm-suppress MixedArgumentTypeCoercion */
            self::unmarshalCollection(
                $propertySubtype,
                $propertyValue,
                $property,
                $object
            );
        } elseif (is_a($propertyType, Date::class, true) && is_scalar($propertyValue)) {
            $object->{$property->getName()} = Date::createFromFormat($propertyDateFormat, (string)$propertyValue);
        } elseif (class_exists($propertyType, true)) {
            $object->{$property->getName()} = self::unmarshal(
                json_encode($propertyValue),
                $propertyType
            );
        } else {
            $object->{$property->getName()} = $propertyValue;
        }
    }
}
