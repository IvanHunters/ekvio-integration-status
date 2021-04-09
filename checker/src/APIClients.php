<?php

declare(strict_types=1);


namespace App;


use Ekvio\Integration\Sdk\V2\EqueoClient;

class APIClients
{
    public array $clients;

    public function addClient(string $title, EqueoClient $client): self
    {
        $this->clients[$title] = $client;
        return $this;
    }

}