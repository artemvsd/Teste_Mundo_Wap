<?php

namespace App\Services;

use Cake\Http\Client;

class ViaCep {
    function findCep(string $cep){
        $client = new Client();

        $response = $client->get("https://viacep.com.br/ws/{$cep}/json/");

        $body = $response->getBody();

        return json_encode($body);
    }
}