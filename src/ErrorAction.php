<?php

declare(strict_types=1);

namespace rucaua\epa\core;

use rucaua\epa\actions\BaseAction;
use Exception;
use JetBrains\PhpStorm\NoReturn;

class ErrorAction extends BaseAction
{

    public function __construct(private Exception $error, private bool $debug = false)
    {
    }

    #[NoReturn] public function run(): void
    {
        header('Content-Type:' . $this->response->getContentType());
        http_response_code(200);
        $data = [
            'message' => $this->error->getMessage(),
            'code' => $this->error->getCode()
        ];
        if ($this->debug) {
            $data['trace'] = $this->error->getTrace();
        }
        echo $this->response->makeString($data);
        exit();
    }
}