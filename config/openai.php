<?php

return [

    'api_key' => env('OPENAI_API_KEY'),

    'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),

    'embedding_dimensions' => env('OPENAI_EMBEDDING_DIMENSIONS', 512),

];
