<?php

declare(strict_types=1);


namespace App;


use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Sdk\ApiException;
use Ekvio\Integration\Sdk\V2\EqueoClient;

class MainStatus
{
    protected array $statusMethods;
    protected Profiler $profiler;
    protected array $exceptionMethods;
    private APIClients $clients;

    public function __construct(APIClients $clients, Profiler $profiler)
    {
        $this->clients = $clients;
        $this->profiler = $profiler;
        $this->statusMethods = [];
        $this->exceptionMethods = [];
    }

    protected function check(string $methodName, string $envName, callable $action) {
        try {
            $action();
        } catch (ApiException $exception) {
            $this->statusMethods[$envName][$methodName] = 'fail';
            $this->exceptionMethods[$envName][$methodName]= $exception->getMessage();
            return;
        }
        $this->statusMethods[$envName][$methodName] = 'success';
    }
}