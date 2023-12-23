<?php

namespace App\Helpers;

use App\Exceptions\ParamNotExistsException;
use Exception;
use stdClass;

class Mssql
{
    protected string $viewPrefix = 'v';

    protected string $paramPrefix = '@';

    protected array $params = [];

    protected string $query;

    /**
     * @throws Exception
     */
    public function queryMaps(string|null $name = null): object|bool
    {
        try {
            $new = new StdClass();
            $new->params = '@user1,@user2, @user3';
            //$new->params = '';
            $new->dbName = '[-bir-][-iki-][--uc--]';

            $new->params = array_filter(array_map(function ($param) {
                return str_replace($this->paramPrefix, '', trim($param));
            }, explode(',', $new->params)));

            //TODO: yoksa hataya at
            return $new;
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
        foreach ($map->params as $param) {
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

    protected function columnPrefix(string $column): string
    {
        return !str_starts_with($column, $this->paramPrefix) ? $this->paramPrefix : '';
    }
}
