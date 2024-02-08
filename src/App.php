<?php

declare(strict_types=1);

namespace rucaua\epa\core;

use rucaua\epa\core\exceptions\ApplicationException;
use rucaua\epa\actions\ActionInterface;
use rucaua\epa\core\exceptions\InvalidConfigException;
use rucaua\epa\core\interfaces\BaseAppConfigInterface;
use rucaua\epa\request\InvalidRequestException;
use rucaua\epa\request\Request;
use rucaua\epa\request\RequestType;
use rucaua\epa\response\ResponseInterface;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use rucaua\epa\actions\EntityInterface;
use rucaua\epa\actions\NotFoundException;


/**
 * Main class using the Singleton pattern. Used as configuration and startup storage for other parts of the application.
 */
class App
{
    private static ?App $instance = null;

    protected function __construct(public readonly BaseAppConfigInterface $config)
    {
    }

    protected function __clone()
    {
    }

    /**
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }


    public static function instance(?BaseAppConfigInterface $config = null): App
    {
        if (!static::$instance) {
            static::$instance = new static($config);
        }
        return self::$instance;
    }

    /**
     * @return void
     * @throws ApplicationException
     * @throws InvalidConfigException
     * @throws InvalidRequestException
     * @throws NotFoundException
     */
    #[NoReturn] public function run(): void
    {
        // TODO move it somewhere
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, api_key, Authorization");

        $id = $this->getSubjectId();
        if ($actionOrEntity = $this->findSubjectByName($this->getSubject())) {
            /** Use build in CRUD actions depends on request type. You can redefine them via config  */
            if ($actionOrEntity instanceof EntityInterface) {
                $type = $this->request()->getType();
                switch ($type) {
                    case RequestType::GET:
                    case RequestType::HEAD:
                        $action = $this->config->getReadAction();
                        break;
                    case RequestType::POST:
                        $action = $this->config->getCreateAction();
                        break;
                    case RequestType::PATCH:
                    case RequestType::PUT:
                        $action = $this->config->getUpdateAction();
                        break;
                    case RequestType::DELETE:
                        $action = $this->config->getDeleteAction();
                        break;
                    case RequestType::OPTIONS:
                        $action = $this->config->getOptionsAction();
                        break;
                    default:
                        throw new ApplicationException('Unknown request type ' . $type->value);
                }
                $action = $action
                    ->setRequest($this->config->getRequest())
                    ->setEntity($actionOrEntity, $id)
                    ->setResponse($this->config->getResponse());
            } elseif ($actionOrEntity instanceof ActionInterface) {
                $action = $actionOrEntity
                    ->setRequest($this->config->getRequest())
                    ->setResponse($this->config->getResponse());
            } else {
                throw new InvalidConfigException();
            }
            try {
                $action->run();
            } catch (Exception $e) {
                //TODO add logger
                (new ErrorAction($e, $this->config->isDebugMode()))
                    ->setRequest($this->config->getRequest())
                    ->setResponse($this->config->getResponse())
                    ->run();
            }
        }
        throw new NotFoundException('Page not found');
    }

    public function request(): Request
    {
        return $this->config->getRequest();
    }

    public function response(): ResponseInterface
    {
        return $this->config->getResponse();
    }

    /**
     * @param string|null $entityName
     * @return ActionInterface|EntityInterface|null
     */
    private function findSubjectByName(?string $entityName = null): ActionInterface|EntityInterface|null
    {
        return $entityName ? $this->config->getEntitiesMap()[$entityName] ?? null : null;
    }


    public function getSubjectId(): ?int
    {
        $path = $this->request()->getPath();
        $last = end($path);
        return is_numeric($last) ? (int)$last : null;
    }

    /**
     * @throws InvalidRequestException
     */
    public function getSubject(): ?string
    {
        $path = $this->request()->getPath();
        $last = array_pop($path);
        return is_numeric($last) ? array_pop($path) : $last;
    }
}