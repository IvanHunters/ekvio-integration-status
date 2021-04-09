<?php

declare(strict_types=1);

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Sdk\V2\Kpi\KpiApi;

class KPIStatus extends MainStatus implements Invoker
{

    private array $kpis;

    public function __construct(APIClients $client, Profiler $profiler)
    {
        parent::__construct($client, $profiler);
        foreach ($client->clients as $clientName => $clientData) {
            $this->kpis[$clientName] = new KpiApi($clientData);
        }
    }

    public function __invoke(array $arguments = [])
    {
        $this->profiler->profile("Check kpi import...");
        $this->kpiImport();

        $statusData = $arguments['prev'] ?? [];
        $statusData['kpi'] = [
            'statusMethods' => $this->statusMethods,
            'exceptionMethods' => $this->exceptionMethods,
        ];

        return $statusData;
    }

    private function kpiImport(): void
    {
        $methodName = 'import';

        foreach ($this->kpis as $envName => $kpiData) {
            $this->check($methodName, $envName, function () use ($kpiData) {
                $kpiData->transactions([
                    [
                        'login' => 'test_kpi',
                        'kpi' => 'sales',
                        'amount' => -100.5,
                        'comment' => 'Отсутствие продаж',
                        'date' => '2020-01-01 12:00:00'
                    ]
                ],
                    [
                        'override' => true
                    ]);
            });
        }
    }

    public function name(): string
    {
        return 'Check kpi methods...';
    }
}