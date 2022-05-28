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

use ArgumentCountError;
use DateTime;
use Feast\Attributes\JsonItem;
use Feast\Collection\Collection;
use Feast\Collection\CollectionList;
use Feast\Collection\Set;
use Feast\Exception\ServerFailureException;
use JsonException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

class Json
{

    /**
     * Marshal an object into a JsonString.
     *
     * The field names are kept as is, unless a Feast\Attributes\JsonItem attribute decorates the property.
     *
     * @param object $object
     * @param int|null $propertyTypesFlag (see https://www.php.net/manual/en/class.reflectionproperty.php#reflectionproperty.constants.modifiers)
     * @return string
     * @throws ReflectionException
     * @see \Feast\Attributes\JsonItem
     */
    public static function marshal(object $object, ?int $propertyTypesFlag = null): string
    {
        $return = new stdClass();
        $paramInfo = self::getClassParamInfo($object::class, $propertyTypesFlag);
        /**
         * @var string $oldName
         * @var array{name:string|null,type:string|null,dateFormat:string,included:bool,omitEmpty:bool} $newInfo
         */
        foreach ($paramInfo as $oldName => $newInfo) {
            if ($newInfo['included'] === false) {
                continue;
            }
            $newName = $newInfo['name'];

            $reflected = new ReflectionProperty($object, $oldName);
            if ($reflected->isInitialized($object)) {
                /** @var scalar|object|array|null $oldItem */
                $oldItem = $reflected->getValue($object);
                if ($newInfo['omitEmpty'] && ($oldItem === null || $oldItem === '')) {
                    continue;
                } elseif (is_array($oldItem) || $oldItem instanceof stdClass) {
                    $return->{$newName} = self::marshalArray((array)$oldItem);
                } elseif (is_object(
                        $oldItem
                    ) && $oldItem instanceof Collection === false && $oldItem instanceof DateTime === false && $oldItem instanceof Date === false) {
                    $return->{$newName} = (object)json_decode(self::marshal($oldItem));
                } elseif ($oldItem instanceof Collection) {
                    $return->{$newName} = self::marshalArray($oldItem->toArray());
                } elseif ($oldItem instanceof Date) {
                    $return->{$newName} = $oldItem->getFormattedDate($newInfo['dateFormat']);
                } elseif ($oldItem instanceof DateTime) {
                    $return->{$newName} = $oldItem->format($newInfo['dateFormat']);
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
     * @param bool $skipConstructor
     * @return object
     * @throws Exception\InvalidDateException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ServerFailureException
     * @see \Feast\Attributes\JsonItem
     */
    public static function unmarshal(string $data, string|object $objectOrClass, bool $skipConstructor = false): object
    {
        if (is_string($objectOrClass)) {
            $object = self::getObjectFromClassString($objectOrClass, $skipConstructor);
        } else {
            $object = $objectOrClass;
        }
        $className = $object::class;
        /** @var array $jsonData */
        $jsonData = json_decode($data, true, flags: JSON_THROW_ON_ERROR);
        $paramInfo = self::getClassParamInfo($className);

        $classInfo = new ReflectionClass($className);
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
                $object,
                $skipConstructor
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
            if (is_array($item) || $item instanceof stdClass) {
                $return[$key] = self::marshalArray((array)$item);
            } elseif (is_object($item)) {
                $return[$key] = (array)json_decode(self::marshal($item));
            } else {
                $return[$key] = $item;
            }
        }

        return $return;
    }

    /**
     * @param class-string $class
     * @param int|null $getPropertyTypeFlag - (see https://www.php.net/manual/en/class.reflectionproperty.php#reflectionproperty.constants.modifiers)
     * @return array<array{name:string|null,type:string|null,dateFormat:string|null,included:bool,omitEmpty:bool}>
     * @throws ReflectionException
     */
    protected static function getClassParamInfo(
        string $class,
        ?int $getPropertyTypeFlag = null
    ): array {
        $return = [];
        $classInfo = new ReflectionClass($class);
        /** @psalm-suppress PossiblyNullArgument - incorrect, PHP 8.0 and above, null is valid */
        foreach ($classInfo->getProperties($getPropertyTypeFlag) as $property) {
            $name = $property->getName();
            $type = null;
            $dateFormat = Date::ATOM;
            $included = true;
            $omitEmpty = false;
            $attributes = $property->getAttributes(JsonItem::class);
            foreach ($attributes as $attribute) {
                /** @var JsonItem $attributeObject */
                $attributeObject = $attribute->newInstance();
                $name = $attributeObject->name ?? $name;
                $type = $attributeObject->arrayOrCollectionType;
                $dateFormat = $attributeObject->dateFormat;
                $included = $attributeObject->included;
                $omitEmpty = $attributeObject->omitEmpty;
            }
            $return[$property->getName()] = [
                'name' => $name,
                'type' => $type,
                'dateFormat' => $dateFormat,
                'included' => $included,
                'omitEmpty' => $omitEmpty
            ];
        }
        return $return;
    }

    /**
     * @param ReflectionProperty $property
     * @param object $object
     * @param string $propertySubtype
     * @param array $jsonData
     * @throws Exception\ServerFailureException
     * @throws ReflectionException|JsonException
     */
    protected static function unmarshalArray(
        ReflectionProperty $property,
        object $object,
        string $propertySubtype,
        array $jsonData
    ): void {
        $item = [];

        if (class_exists($propertySubtype, true)) {
            /**
             * @var string $key
             * @var scalar|object|array $val
             */
            foreach ($jsonData as $key => $val) {
                $item[$key] = self::unmarshal(
                    json_encode($val),
                    $propertySubtype
                );
            }
        } else {
            $item = $jsonData;
        }
        $property->setValue($object, $item);
    }

    /**
     * @param string $propertySubtype
     * @param array<string|int|bool|float|object|array> $jsonData
     * @param ReflectionProperty $property
     * @param object $object
     * @throws Exception\ServerFailureException|ReflectionException
     * @throws JsonException
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
        $property->setValue(
            $object,
            new Set(
                              $propertySubtype,
                              $jsonData,
                preValidated: true
            )
        );
    }

    /**
     * @param string $propertySubtype
     * @param array<array> $jsonData
     * @param ReflectionProperty $property
     * @param object $object
     * @throws Exception\ServerFailureException|ReflectionException
     * @throws JsonException
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
        $property->setValue(
            $object,
            new CollectionList(
                              $propertySubtype,
                              $jsonData,
                preValidated: true
            )
        );
    }

    /**
     * @param array $jsonData
     * @param string $propertySubtype
     * @return array<string|int|bool|float|object|array>
     * @throws Exception\ServerFailureException
     * @throws ReflectionException
     * @throws JsonException
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
     * Unmarshal a property into stdClass.
     *
     * @param ReflectionProperty $property
     * @param object $object
     * @param array $jsonData
     */
    protected static function unmarshalStdClass(
        ReflectionProperty $property,
        object $object,
        array $jsonData
    ): void {
        $property->setValue($object, (object)json_decode(json_encode($jsonData)));
    }

    /**
     * Unmarshal a property onto the object.
     *
     * @param ReflectionProperty $property
     * @param string $propertySubtype
     * @param string $propertyDateFormat
     * @param scalar|array $propertyValue
     * @param object $object
     * @param bool $skipConstructor
     * @throws Exception\InvalidDateException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ServerFailureException
     */
    protected static function unmarshalProperty(
        ReflectionProperty $property,
        string $propertySubtype,
        string $propertyDateFormat,
        mixed $propertyValue,
        object $object,
        bool $skipConstructor
    ): void {
        $propertyType = (string)$property->getType();

        if ($propertyType === 'array' && is_array($propertyValue)) {
            self::unmarshalArray(
                $property,
                $object,
                $propertySubtype,
                $propertyValue,
            );
        } elseif ($propertyType === stdClass::class && is_array($propertyValue)) {
            self::unmarshalStdClass($property, $object, $propertyValue);
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
            $property->setValue($object, Date::createFromFormat($propertyDateFormat, (string)$propertyValue));
        } elseif (is_a($propertyType, DateTime::class, true) && is_scalar($propertyValue)) {
            $property->setValue($object, DateTime::createFromFormat($propertyDateFormat, (string)$propertyValue));
        } elseif (class_exists($propertyType, true)) {
            $property->setValue(
                $object,
                self::unmarshal(
                    json_encode($propertyValue),
                    $propertyType,
                    $skipConstructor
                )
            );
        } else {
            $property->setValue($object, $propertyValue);
        }
    }

    /**
     * @param class-string $className
     * @param bool $skipConstructor
     * @return object
     * @throws ReflectionException
     * @throws ServerFailureException
     */
    protected static function getObjectFromClassString(string $className, bool $skipConstructor): object
    {
        if ($skipConstructor === false) {
            try {
                /** @psalm-suppress MixedMethodCall */
                $object = new $className();
            } catch (ArgumentCountError) {
                throw new ServerFailureException(
                    'Attempted to unmarshal into a class without a no-argument capable constructor'
                );
            }
        } else {
            $class = new ReflectionClass($className);
            $object = $class->newInstanceWithoutConstructor();
        }
        return $object;
    }
}
