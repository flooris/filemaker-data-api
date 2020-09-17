<?php


namespace Flooris\FileMakerDataApi\Api;


use Exception;

class Record extends ApiAbstract
{

    /**
     * @param array $fieldData
     *
     * @return object
     * @throws Exception
     */
    public function createNewRecord($fieldData)
    {
        return $this->post('records', [], [
            'fieldData' => $fieldData,
        ]);
    }

    /**
     * @param int   $recordId
     * @param array $fieldData
     *
     * @return object
     * @throws Exception
     */
    public function editRecord($recordId, $fieldData)
    {
        $parameters = [
            'fieldData' => $fieldData,
        ];

        return $this->patch('records/%s', [$recordId], $parameters);
    }

    /**
     * @param int $recordId
     *
     * @return object
     * @throws Exception
     */
    public function deleteRecord($recordId)
    {
        return $this->delete('records/%s', [$recordId]);
    }

    /**
     * @param int $recordId
     *
     * @return object
     * @throws Exception
     */
    public function duplicateRecord($recordId)
    {
        return $this->post('records/%s', [$recordId]);
    }

    /**
     * @param int $recordId
     *
     * @return object
     * @throws Exception
     */
    public function singleRecord($recordId)
    {
        return $this->get('records/%s', [$recordId]);
    }

    /**
     * Get records, default limit is 100 when called without offset or limit.
     *
     * @param int        $starting_record
     * @param int        $limit
     * @param array|null $sort , example: ['fieldName' => 'NAME', 'sortOrder' => 'ascend']
     *
     * @return object
     * @throws Exception
     * @see  https://fmhelp.filemaker.com/docs/18/en/dataapi/#work-with-records_get-records
     */
    public function records($starting_record = 1, $limit = 100, $sort = null)
    {
        $query = [
            '_offset' => $starting_record,
            '_limit'  => $limit,
        ];

        if (is_array($sort) && !empty($sort)) {
            if (! isset($sort[0]) || !is_array($sort[0])) {
                $sort = [$sort]; // This is required as FileMaker expects an array
            }

            $query['_sort'] = json_encode($sort);
        }

        return $this->get('records', [], $query);
    }

    /**
     * Perform a search for a record / multiple records based on specified in the query
     *
     * @param array      $query
     * @param int        $offset
     * @param int        $limit
     * @param array|null $sort
     *
     * @return object
     * @throws Exception
     * @see https://fmhelp.filemaker.com/docs/18/en/dataapi/#perform-a-find-request
     */
    public function findRecords($query, $offset = 1, $limit = 100, $sort = null)
    {
        if (! isset($query[0]) || gettype($query[0]) !== 'array') {
            $query = [$query]; // This is required as FileMaker expects an array
        }

        $parameters = [
            'query'  => $query,
            'limit'  => $limit,
            'offset' => $offset,
        ];

        if (is_array($sort) && !empty($sort)) {
            if (! isset($sort[0]) || !is_array($sort[0])) {
                $sort = [$sort]; // This is required as FileMaker expects an array
            }

            $parameters['sort'] = $sort;
        }

        return $this->post('_find', [], $parameters);
    }
}