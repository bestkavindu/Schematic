<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allow pushing to private / reserved hosts
    |--------------------------------------------------------------------------
    |
    | When false (default) the "Push to Postgres / Supabase" feature refuses to
    | connect to private or reserved IP ranges (localhost, 10.x, 192.168.x, the
    | cloud metadata endpoint, ...) as an SSRF safeguard. Enable it only to test
    | against a local Postgres (e.g. Docker on 127.0.0.1).
    |
    */

    'allow_private_db_hosts' => (bool) env('SCHEMATIC_ALLOW_PRIVATE_DB_HOSTS', false),

];
