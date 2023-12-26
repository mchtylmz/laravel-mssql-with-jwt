<?php

namespace App\Helpers;

use App\Exceptions\ParamNotExistsException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Mssql
{
    /**
     * @var string
     */
    protected string $viewPrefix = 'v';
    /**
     * @var string
     */
    protected string $mapTableName = 'VisioMedia.util.ApiMap';

    /**
     * @var string
     */
    protected string $paramPrefix = '@';

    /**
     * @var string
     */
    protected string $paramsSeparator = '|';

    /**
     * @var array
     */
    protected array $params = [];

    /**
     * @var
     */
    protected $map;

    /**
     * @throws Exception
     */
    public function queryMaps(string|null $name = null): object|bool
    {
        try {
            $this->map = cache()->remember('mssql_map_'.$name, 21600, function () use($name) {
                return DB::table($this->mapTableName)
                    ->where('IsActive', 1)
                    ->where('ApiName', $name)
                    ->orderBy('ID', 'DESC')
                    ->first();
            });

            if (!$this->map) {
                throw new Exception('Not found action map list!', 500);
            }

            $this->map->Params = array_filter(array_map(function ($param) {
                return str_replace($this->paramPrefix, '', trim($param));
            }, explode($this->paramsSeparator, $this->map->Params)));

            return $this->map;
        } catch (Exception $error) {
            throw new Exception($error->getMessage());
        }
    }


    /**
     * @param array $queryParams
     * @param string $separator
     * @return string
     */
    public function processParams(array $queryParams, string $separator = ','): string
    {
        $params = [];

        foreach ($this->params as $column) {
            $value = $queryParams[$column] ?? false;
            if ($value) {
                $params[] = sprintf(" %s = '%s'", $this->columnPrefix($column).$column, $value);
            }
        }

        return implode($separator, $params);
    }


    /**
     * @param string $queryName
     * @param array $queryParams
     * @return $this
     * @throws ParamNotExistsException
     * @throws Exception
     */
    public function valid(string $queryName, array $queryParams = []): self
    {
        $map = $this->queryMaps($queryName);
        foreach ($map->Params as $param) {
            if (!array_key_exists($param, $queryParams)) {
                throw new ParamNotExistsException(sprintf(
                    "%s param not exists", $param
                ));
            }
            $this->params[] = $param;
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function query(string $queryName, array|null $queryParams = []): string
    {
        $this->valid($queryName, $queryParams);

        $separator = str_starts_with($queryName, $this->viewPrefix) ? 'AND' : ',';
        if ($separator === 'AND') {
            return sprintf(
                "SELECT * FROM %s WHERE %s", $this->map->DbName, $this->processParams($queryParams, $separator)
            );
        }

        return sprintf(
            "EXEC %s %s", $this->map->DbName, $this->processParams($queryParams, $separator)
        );
    }

    /**
     * @throws Exception
     */
    public function run(string $query, bool $single = false): array|object
    {
        try {
            $result = DB::select($query);
            return $single && !empty($result[0]) ? $result[0] : $result;
        } catch (Exception $error) {
            throw new Exception($error->getMessage(), 500);
        }
    }

    public function generateVerifyCode(int $user_id): array
    {
        $code = 123456; // Str::random(6);
        return [$code];
        return $this->run(sprintf(
            "EXEC __otp__ @userId = '%s', @code = '%s'",
            $user_id,
            $code
        ));
    }

    /**
     * @param string $column
     * @return string
     */
    protected function columnPrefix(string $column): string
    {
        return !str_starts_with($column, $this->paramPrefix) ? $this->paramPrefix : '';
    }
}
