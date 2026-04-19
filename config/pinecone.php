<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pinecone (vector index)
    |--------------------------------------------------------------------------
    |
    | Vectors are NOT stored in MySQL/SQLite — only metadata + file refs live
    | in the app DB. Embeddings go to Pinecone via HTTP API.
    |
    | Serverless: set PINECONE_INDEX_HOST from the Pinecone console (per index).
    |
    */

    'api_key' => env('PINECONE_API_KEY'),

    'index_host' => env('PINECONE_INDEX_HOST'),

    'index_name' => env('PINECONE_INDEX_NAME'),

];
