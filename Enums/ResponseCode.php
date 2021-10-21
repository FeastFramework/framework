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

enum ResponseCode: int
{
    case HTTP_CODE_100 = 100;
    case HTTP_CODE_101 = 101;
    case HTTP_CODE_102 = 102;
    case HTTP_CODE_200 = 200;
    case HTTP_CODE_201 = 201;
    case HTTP_CODE_202 = 202;
    case HTTP_CODE_203 = 203;
    case HTTP_CODE_204 = 204;
    case HTTP_CODE_205 = 205;
    case HTTP_CODE_206 = 206;
    case HTTP_CODE_207 = 207;
    case HTTP_CODE_300 = 300;
    case HTTP_CODE_301 = 301;
    case HTTP_CODE_302 = 302;
    case HTTP_CODE_303 = 303;
    case HTTP_CODE_304 = 304;
    case HTTP_CODE_305 = 305;
    case HTTP_CODE_306 = 306;
    case HTTP_CODE_307 = 307;
    case HTTP_CODE_308 = 308;
    case HTTP_CODE_400 = 400;
    case HTTP_CODE_401 = 401;
    case HTTP_CODE_402 = 402;
    case HTTP_CODE_403 = 403;
    case HTTP_CODE_404 = 404;
    case HTTP_CODE_405 = 405;
    case HTTP_CODE_406 = 406;
    case HTTP_CODE_407 = 407;
    case HTTP_CODE_408 = 408;
    case HTTP_CODE_409 = 409;
    case HTTP_CODE_410 = 410;
    case HTTP_CODE_411 = 411;
    case HTTP_CODE_412 = 412;
    case HTTP_CODE_413 = 413;
    case HTTP_CODE_414 = 414;
    case HTTP_CODE_415 = 415;
    case HTTP_CODE_416 = 416;
    case HTTP_CODE_417 = 417;
    case HTTP_CODE_418 = 418;
    case HTTP_CODE_419 = 419;
    case HTTP_CODE_420 = 420;
    case HTTP_CODE_422 = 422;
    case HTTP_CODE_423 = 423;
    case HTTP_CODE_424 = 424;
    case HTTP_CODE_425 = 425;
    case HTTP_CODE_426 = 426;
    case HTTP_CODE_428 = 428;
    case HTTP_CODE_429 = 429;
    case HTTP_CODE_431 = 431;
    case HTTP_CODE_444 = 444;
    case HTTP_CODE_449 = 449;
    case HTTP_CODE_450 = 450;
    case HTTP_CODE_451 = 451;
    case HTTP_CODE_494 = 494;
    case HTTP_CODE_495 = 495;
    case HTTP_CODE_496 = 496;
    case HTTP_CODE_497 = 497;
    case HTTP_CODE_499 = 499;
    case HTTP_CODE_500 = 500;
    case HTTP_CODE_501 = 501;
    case HTTP_CODE_502 = 502;
    case HTTP_CODE_503 = 503;
    case HTTP_CODE_504 = 504;
    case HTTP_CODE_505 = 505;
    case HTTP_CODE_506 = 506;
    case HTTP_CODE_507 = 507;
    case HTTP_CODE_508 = 508;
    case HTTP_CODE_509 = 509;
    case HTTP_CODE_510 = 510;
    case HTTP_CODE_511 = 511;
    case HTTP_CODE_598 = 598;
    case HTTP_CODE_599 = 599;

    /**
     * Returns if response code is valid.
     *
     * @param int $responseCode
     * @return bool
     */
    public static function isValidResponseCode(int $responseCode): bool
    {
        return defined('self::HTTP_CODE_' . (string)$responseCode);
    }
}
