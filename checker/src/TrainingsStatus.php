<?php

declare(strict_types=1);

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Sdk\V2\EqueoClient;
use Ekvio\Integration\Sdk\V2\Training\Training;
use Ekvio\Integration\Sdk\V2\Training\TrainingApi;
use Ekvio\Integration\Sdk\V2\Training\TrainingSearchCriteria;
use Ekvio\Integration\Sdk\V2\Training\TrainingStatisticCriteria;

class TrainingsStatus extends MainStatus implements Invoker
{

    private array $trainings;

    public function __construct(APIClients $client, Profiler $profiler)
    {
        parent::__construct($client, $profiler);
        foreach ($client->clients as $clientName => $clientData) {
            $this->trainings[$clientName] = new TrainingApi($clientData);
        }
    }

    public function __invoke(array $arguments = [])
    {
        $this->profiler->profile("Check training search...");
        $this->trainingsSearch();
        $this->profiler->profile("Check training statistics...");
        $this->trainingsStatistics();

        $statusData = $arguments['prev'] ?? [];
        $statusData['trainings'] = [
            'statusMethods' => $this->statusMethods,
            'exceptionMethods' => $this->exceptionMethods,
        ];

        return $statusData;
    }

    private function trainingsSearch(): void
    {
        $methodName = 'search';

        foreach ($this->trainings as $envName => $trainingsData) {
            $this->check($methodName, $envName, function () use ($trainingsData) {
                $criteria = TrainingSearchCriteria::build();
                $trainingsData->search($criteria);
            });
        }
    }

    private function trainingsStatistics(): void
    {
        $methodName = 'statistics';

        foreach ($this->trainings as $envName => $trainingsData) {
            $this->check($methodName, $envName, function () use ($trainingsData) {
                $criteria = TrainingStatisticCriteria::build();
                $trainingsData->statistic($criteria);
            });
        }
    }

    public function name(): string
    {
        return 'Check trainings methods...';
    }
}