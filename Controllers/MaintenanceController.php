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

namespace Feast\Controllers;

use Feast\Attributes\Action;
use Feast\CliController;
use Feast\View;

class MaintenanceController extends CliController
{
    protected const MAINTENANCE_VIEW = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Error' . DIRECTORY_SEPARATOR . 'maintenance-screen.phtml';

    #[Action(description: 'Start maintenance mode.')]
    public function startGet(): void
    {
        if (!file_exists(self::MAINTENANCE_VIEW)) {
            file_put_contents(self::MAINTENANCE_VIEW, 'The website is undergoing maintenance.');
        }

        file_put_contents(APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'maintenance.txt', '1');

        ob_start();
        di(View::class)->showView('Error', 'maintenance-screen');
        file_put_contents(
            APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'maintenance-screen.html',
            ob_get_clean()
        );

        $this->terminal->command('Maintenance mode enabled!');
        $this->terminal->command(
            'Views/Error/maintenance-screen.phtml has been generated to the public folder as maintenance-screen.html'
        );
    }

    #[Action(description: 'Stop maintenance mode.')]
    public function stopGet(): void
    {
        if (file_exists(APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'maintenance.txt')) {
            unlink(APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'maintenance.txt');
            file_put_contents(
                APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'maintenance-screen.html',
                '<script type="text/javascript">window.location.href = "/";</script>'
            );
            $this->terminal->command('Maintenance mode disabled!');
            $this->terminal->command('Requests to maintenance screen will return to the index');
        }
    }

}
