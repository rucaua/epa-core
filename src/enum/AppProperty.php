<?php

namespace rucaua\epa\core\enum;

use rucaua\epa\core\App;

enum AppProperty:string
{
    /**
     * @see App::$request
     */
    case REQUEST = 'request';
    case RESPONSE = 'response';
    case ROUTING = 'routing';
    case CONNECTION = 'connection';
}
