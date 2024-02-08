<?php

namespace rucaua\epa\core\interfaces;


use rucaua\epa\request\RequestInterface;
use rucaua\epa\response\ResponseInterface;
use rucaua\epa\actions\ActionInterface;

interface BaseAppConfigInterface
{
    public function getRequest(): RequestInterface;

    public function getResponse(): ResponseInterface;

    public function getConnection(): ConnectionInterface;

    public function getEntitiesMap(): array;

    public function getCreateAction(): ActionInterface;

    public function getReadAction(): ActionInterface;

    public function getUpdateAction(): ActionInterface;

    public function getDeleteAction(): ActionInterface;

    public function getOptionsAction(): ActionInterface;

    public function isDebugMode(): bool;
}