<?php

namespace Flooris\FileMakerDataApi\Api;

use Exception;
use Psr\SimpleCache\InvalidArgumentException;

class Record extends ApiAbstract
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function createNewRecord(array $fieldData): object
    {
        return $this->post('records', [], [
            'fieldData' => $fieldData,
        ]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function editRecord(int $recordId, array $fieldData): object
    {
        $parameters = [
            'fieldData' => $fieldData,
        ];

        return $this->patch('records/%s', [$recordId], $parameters);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function deleteRecord(int $recordId): object
    {
        return $this->delete('records/%s', [$recordId]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function duplicateRecord(int $recordId): object
    {
        return $this->post('records/%s', [$recordId]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function singleRecord(int $recordId): object
    {
        return $this->get('records/%s', [$recordId]);
    }

    /**
     * Get records, default limit is 100 when called without offset or limit.
     *
     * @throws Exception|InvalidArgumentException
     *
     * @see  https://fmhelp.filemaker.com/docs/18/en/dataapi/#work-with-records_get-records
     */
    public function records(int $startingRecord = 1, int $limit = 100, ?array $sort = null): object
    {
        $query = [
            '_offset' => $startingRecord,
            '_limit'  => $limit,
        ];

        if (is_array($sort) && ! empty($sort)) {
            if (! isset($sort[0]) || ! is_array($sort[0])) {
                $sort = [$sort]; // This is required as FileMaker expects an array
            }

            $query['_sort'] = json_encode($sort);
        }

        return $this->get('records', [], $query);
    }

    /**
     * Perform a search for a record / multiple records based on specified in the query
     *
     * @throws Exception|InvalidArgumentException
     *
     * @see https://fmhelp.filemaker.com/docs/18/en/dataapi/#perform-a-find-request
     */
    public function findRecords(array $query, int $offset = 1, int $limit = 100, ?array $sort = null): object
    {
        if (! isset($query[0]) || gettype($query[0]) !== 'array') {
            $query = [$query]; // This is required as FileMaker expects an array
        }

        $parameters = [
            'query'  => $query,
            'limit'  => $limit,
            'offset' => $offset,
        ];

        if (is_array($sort) && ! empty($sort)) {
            if (! isset($sort[0]) || ! is_array($sort[0])) {
                $sort = [$sort]; // This is required as FileMaker expects an array
            }

            $parameters['sort'] = $sort;
        }

        return $this->post('_find', [], $parameters);
    }
}
