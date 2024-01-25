<?php

namespace rucaua\epa\core\interfaces;

use rucaua\epa\request\RequestInterface;
use rucaua\epa\response\ResponseInterface;
use JetBrains\PhpStorm\NoReturn;

interface ActionInterface
{
    #[NoReturn] public function run(): void;

    /**
     * Method for passing request data into an action
     * @return $this
     */
    public function setRequest(RequestInterface $request): self;
    public function setResponse(ResponseInterface $response): self;
}