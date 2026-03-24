<?php

use Facil\Http\Response;

return [
    'name' => 'api.products.show',
    'GET' => function(string $id) {
        return Response::json(['product_id' => $id]);
    }
];