<?php

use Facil\Http\HttpStatusCode;
use Facil\Http\Request;
use Facil\Http\Response;
use Facil\Support\Validate;

return [
    'name' => 'api.users',
    'GET' => function() {
        return Response::json([
            'message' => 'List of users fetched perfectly.',
            'headers' => Request::headers()
        ]);
    },
    
    'POST' => function() {
        $data = Request::body();
        
        if (!Validate::cpf($data['cpf'] ?? '')) {
            return Response::json(['error' => 'Invalid CPF'], HttpStatusCode::BAD_REQUEST);
        }

        return Response::json(['status' => 'User created'], HttpStatusCode::CREATED);
    }
];