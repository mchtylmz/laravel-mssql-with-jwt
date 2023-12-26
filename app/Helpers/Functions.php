<?php

if (!function_exists('mssql')) {
    function mssql()
    {
        return new App\Helpers\Mssql();
    }
}

if (!function_exists('procedure')) {
    function procedure(string $name, $data = [], bool $single = false)
    {
        $query = mssql()->query($name, $data);
        return mssql()->run($query, $single);
    }
}

if (!function_exists('isFailed')) {
    function isFailed($response): bool
    {
        if (is_array($response) && !empty($response[0]->Result) && $response[0]->Result == 400) {
            return true;
        }
        elseif (is_object($response) && !empty($response->Result) && $response->Result == 400) {
            return true;
        }
        return false;
    }
}
