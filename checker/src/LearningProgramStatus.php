<?php

declare(strict_types=1);

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Sdk\V2\LearningProgram\ProgramApi;
use Ekvio\Integration\Sdk\V2\LearningProgram\ProgramSearchCriteria;
use Ekvio\Integration\Sdk\V2\LearningProgram\ProgramStatisticCriteria;

class LearningProgramStatus extends MainStatus implements Invoker
{

    private array $programs;

    public function __construct(APIClients $client, Profiler $profiler)
    {
        parent::__construct($client, $profiler);
        foreach ($client->clients as $clientName => $clientData) {
            $this->programs[$clientName] = new ProgramApi($clientData);
        }
    }

    public function __invoke(array $arguments = [])
    {
        $this->profiler->profile("Check program search...");
        $this->programSearch();
        $this->profiler->profile("Check program statistic...");
        $this->programStatistics();

        $statusData = $arguments['prev'] ?? [];
        $statusData['programs'] = [
            'statusMethods' => $this->statusMethods,
            'exceptionMethods' => $this->exceptionMethods,
        ];

        return $statusData;
    }

    private function programSearch(): void
    {
        $methodName = 'search';
        foreach ($this->programs as $envName => $programData) {
            $this->check($methodName, $envName, function () use ($programData) {
                $criteria = ProgramSearchCriteria::build();
                $programData->search($criteria);
            });
        }
    }

    private function programStatistics(): void
    {
        $methodName = 'statistics';
        foreach ($this->programs as $envName => $programData) {
            $this->check($methodName, $envName, function () use ($programData) {
                $criteria = ProgramStatisticCriteria::build();
                $programData->statistic($criteria);
            });
        }
    }

    public function name(): string
    {
        return 'Check learning programs methods...';
    }
}