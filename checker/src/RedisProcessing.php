<?php

declare(strict_types=1);


namespace App;


use Ekvio\Integration\Contracts\Invoker;
use Redis;

class RedisProcessing implements Invoker
{

    private Redis $redis;

    public function __construct(Redis $redis) {
        $this->redis = $redis;
    }

    public function __invoke(array $arguments = [])
    {
        if (isset($arguments['prev'])) {
            $statusInfo = $arguments['prev'];
            $this->handleStatuses($statusInfo);
        }
    }

    private function handleStatuses(array $statusInfo) {
        $mainData = [];
        $methods = [];
        foreach ($statusInfo as $statusName => $statusData) {
            $statuses = $statusData['statusMethods'];

            if (count($statusData['exceptionMethods']) === 0) {
                foreach ($statuses as $env => $envData){
                    $statusData[$env]['generalStatus'] = 'OK';
                    foreach ($envData as $fieldName => $status) {
                        $statusData[$env]['statusMethods'][$fieldName] = 'success';
                    }
                }
            } else {
                $exceptions = $statusData['exceptionMethods'];
                foreach ($statuses as $env => $envData) {
                    foreach ($envData as $fieldName => $status) {
                        if ($status === 'fail') {
                            $statuses[$fieldName] = [
                                'status' => 'fail',
                                'message' => $exceptions[$env][$fieldName]
                            ];
                        }
                        $statusData[$env]['generalStatus'] = 'Errors';
                    }
                    $statusData[$env]['statusMethods'] = $statuses;
                }
            }
            $envs = array_keys($statusData['statusMethods']);
            foreach ($envs as $env){
                $statusData[$env]['statusMethods'] = $statusData[$env]['statusMethods'];
                $methods = array_keys($statusData['statusMethods'][$env]);
            }
            unset($statusData['statusMethods']);
            unset($statusData['exceptionMethods']);
            $mainData[$statusName] = [
                'data' => $statusData,
                'methods' => $methods
            ];
        }
        $this->redis->set('statistics', json_encode($mainData, JSON_UNESCAPED_UNICODE));
    }

    public function name(): string
    {
        return 'Redis handling statuses';
    }
}