<?php

return [
    (new \Mocks\MockCronJob())->withoutOverlapping()->runInBackground(),

];