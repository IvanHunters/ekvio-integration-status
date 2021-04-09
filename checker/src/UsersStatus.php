<?php

declare(strict_types=1);

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Sdk\V2\User\UserApi;
use Ekvio\Integration\Sdk\V2\User\UserDeleteCriteria;
use Ekvio\Integration\Sdk\V2\User\UserSearchCriteria;

class UsersStatus extends MainStatus implements Invoker
{

    private array $users;

    public function __construct(APIClients $client, Profiler $profiler)
    {
        parent::__construct($client, $profiler);
        foreach ($client->clients as $clientName => $clientData) {
            $this->users[$clientName] = new UserApi($clientData);
        }
    }

    public function __invoke(array $arguments = [])
    {
        $this->profiler->profile("Check users search...");
        $this->usersSearch();
        $this->profiler->profile("Check users sync...");
        $this->usersSync();
        $this->profiler->profile("Check users rename...");
        $this->usersRename();
        $this->profiler->profile("Check users delete...");
        $this->usersDelete();

        $statusData = $arguments['prev'] ?? [];
        $statusData['users'] = [
            'statusMethods' => $this->statusMethods,
            'exceptionMethods' => $this->exceptionMethods,
        ];

        return $statusData;
    }

    private function usersSync(): void
    {
        $methodName = 'sync';

        $user = [
            [
            'login' => 'test',
            'first_name'=> 'Тест',
            'last_name' => 'Тест',
            'email' => 'test@test.ru',
            'phone' => '79999999999',
            'verified_email' =>true,
            'verified_phone' => false,
            'chief_email' => 'testuser@test.ru',
            'status' => 'active',
            'groups' => [
                'region' => 'Region',
                'city' => 'City',
                'role' => 'Role',
                'position' => 'Position',
                'team' => 'Team',
                'department' => 'Department',
                'assignment' => 'Assignment'
                ]
            ],
            [
                'login' => 'test_kpi',
                'first_name'=> 'Тест',
                'last_name' => 'Тест',
                'email' => 'testkpi@test.ru',
                'phone' => '79996498787',
                'verified_email' =>true,
                'verified_phone' => false,
                'chief_email' => 'testuserkpi@test.ru',
                'status' => 'active',
                'groups' => [
                    'region' => 'Region',
                    'city' => 'City',
                    'role' => 'Role',
                    'position' => 'Position',
                    'team' => 'Team',
                    'department' => 'Department',
                    'assignment' => 'Assignment'
                ]
            ]
        ];
        foreach ($this->users as $envName => $userData) {
            $this->check($methodName, $envName, function () use ($userData, $user) {
                $userData->sync($user);
            });
        }
    }

    private function usersSearch(): void
    {
        $methodName = 'search';
        foreach ($this->users as $envName => $userData) {
            $this->check($methodName, $envName, function () use ($userData) {
                $criteria = UserSearchCriteria::createFrom([]);
                $userData->search($criteria);
            });
        }
    }

    private function usersRename(): void
    {
        $methodName = 'rename';
        foreach ($this->users as $envName => $userData) {
            $this->check($methodName, $envName, function () use ($userData) {
                $userData->rename(
                    [
                        [
                            'from' => 'test',
                            'to' => 'test2'
                        ]
                    ]
                );
            });
        }
    }

    private function usersDelete(): void
    {
        $methodName = 'delete';
        foreach ($this->users as $envName => $userData) {
            $this->check($methodName, $envName, function () use ($userData) {
                $criteria = UserDeleteCriteria::createFrom([
                    'login' => [
                        'test2'
                    ]
                ]);
                $userData->delete($criteria);
            });
        }
    }

    public function name(): string
    {
        return 'Check users methods...';
    }
}