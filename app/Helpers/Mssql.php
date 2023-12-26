<?php

namespace App\Helpers;

use App\Exceptions\ParamNotExistsException;
use Exception;
use Illuminate\Support\Facades\DB;
use stdClass;

class Mssql
{
    protected string $viewPrefix = 'v';
    protected string $mapTableName = 'map';

    protected string $paramPrefix = '@';

    protected string $paramsSeparator = '|';

    protected array $params = [];

    protected string $query;

    /**
     * @throws Exception
     */
    public function queryMaps(string|null $name = null): object|bool
    {
        try {
            $map = DB::table($this->mapTableName)
                ->where('IsActive', 1)
                ->where('ApiName', $name)
                ->orderBy('ID', 'DESC')
                ->first();

            if (!$map) {
                throw new Exception('Işlem bilgi haritası bulunamadı!', 500);
            }

            $map->Params = array_filter(array_map(function ($param) {
                return str_replace($this->paramPrefix, '', trim($param));
            }, explode($this->paramsSeparator, $map->Params)));

            return $map;
        } catch (Exception $error) {
            throw new Exception($error->getMessage());
        }
    }

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
                    "%s bulunamadı", $param
                ));
            }
            $this->params[] = $param;
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function query(string $queryName, array $queryParams = []): string
    {
        $this->valid($queryName, $queryParams);

        $separator = str_starts_with($queryName, $this->viewPrefix) ? 'AND' : ',';
        if ($separator === 'AND') {
            return sprintf(
                "SELECT * FROM %s WHERE %s", $queryName, $this->processParams($queryParams, $separator)
            );
        }

        return sprintf(
            "EXEC %s %s", $queryName, $this->processParams($queryParams, $separator)
        );
    }

    /**
     * @throws Exception
     */
    public function run(string $query): array
    {
        try {
            return DB::select($query);
        } catch (Exception $error) {
            throw new Exception($error->getMessage(), 500);
        }
    }

    protected function columnPrefix(string $column): string
    {
        return !str_starts_with($column, $this->paramPrefix) ? $this->paramPrefix : '';
    }
}
