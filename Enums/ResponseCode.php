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

namespace Feast\Enums;

class ResponseCode {
    public const HTTP_CODE_100 = 100;
    public const HTTP_CODE_101 = 101;
    public const HTTP_CODE_102 = 102;
    public const HTTP_CODE_200 = 200;
    public const HTTP_CODE_201 = 201;
    public const HTTP_CODE_202 = 202;
    public const HTTP_CODE_203 = 203;
    public const HTTP_CODE_204 = 204;
    public const HTTP_CODE_205 = 205;
    public const HTTP_CODE_206 = 206;
    public const HTTP_CODE_207 = 207;
    public const HTTP_CODE_300 = 300;
    public const HTTP_CODE_301 = 301;
    public const HTTP_CODE_302 = 302;
    public const HTTP_CODE_303 = 303;
    public const HTTP_CODE_304 = 304;
    public const HTTP_CODE_305 = 305;
    public const HTTP_CODE_306 = 306;
    public const HTTP_CODE_307 = 307;
    public const HTTP_CODE_308 = 308;
    public const HTTP_CODE_400 = 400;
    public const HTTP_CODE_401 = 401;
    public const HTTP_CODE_402 = 402;
    public const HTTP_CODE_403 = 403;
    public const HTTP_CODE_404 = 404;
    public const HTTP_CODE_405 = 405;
    public const HTTP_CODE_406 = 406;
    public const HTTP_CODE_407 = 407;
    public const HTTP_CODE_408 = 408;
    public const HTTP_CODE_409 = 409;
    public const HTTP_CODE_410 = 410;
    public const HTTP_CODE_411 = 411;
    public const HTTP_CODE_412 = 412;
    public const HTTP_CODE_413 = 413;
    public const HTTP_CODE_414 = 414;
    public const HTTP_CODE_415 = 415;
    public const HTTP_CODE_416 = 416;
    public const HTTP_CODE_417 = 417;
    public const HTTP_CODE_418 = 418;
    public const HTTP_CODE_419 = 419;
    public const HTTP_CODE_420 = 420;
    public const HTTP_CODE_422 = 422;
    public const HTTP_CODE_423 = 423;
    public const HTTP_CODE_424 = 424;
    public const HTTP_CODE_425 = 425;
    public const HTTP_CODE_426 = 426;
    public const HTTP_CODE_428 = 428;
    public const HTTP_CODE_429 = 429;
    public const HTTP_CODE_431 = 431;
    public const HTTP_CODE_444 = 444;
    public const HTTP_CODE_449 = 449;
    public const HTTP_CODE_450 = 450;
    public const HTTP_CODE_451 = 451;
    public const HTTP_CODE_494 = 494;
    public const HTTP_CODE_495 = 495;
    public const HTTP_CODE_496 = 496;
    public const HTTP_CODE_497 = 497;
    public const HTTP_CODE_499 = 499;
    public const HTTP_CODE_500 = 500;
    public const HTTP_CODE_501 = 501;
    public const HTTP_CODE_502 = 502;
    public const HTTP_CODE_503 = 503;
    public const HTTP_CODE_504 = 504;
    public const HTTP_CODE_505 = 505;
    public const HTTP_CODE_506 = 506;
    public const HTTP_CODE_507 = 507;
    public const HTTP_CODE_508 = 508;
    public const HTTP_CODE_509 = 509;
    public const HTTP_CODE_510 = 510;
    public const HTTP_CODE_511 = 511;
    public const HTTP_CODE_598 = 598;
    public const HTTP_CODE_599 = 599;

    /**
     * Returns if response code is valid.
     * 
     * @param int $responseCode
     * @return bool
     */
    public static function isValidResponseCode(int $responseCode): bool {
        return defined('self::HTTP_CODE_' . (string)$responseCode);
    }
}
