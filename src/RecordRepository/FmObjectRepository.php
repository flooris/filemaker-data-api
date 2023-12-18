<?php

namespace Flooris\FileMakerDataApi\RecordRepository;

use Flooris\FileMakerDataApi\FileMakerDataApi;

class FmObjectRepository
{
    private $result = null;

    public function __construct(
        private readonly FileMakerDataApi $fmClient,
        public readonly FmObject $fmObject,
    ) {
    }

    public function find(int $id)
    {
        return $this->findMany([$this->fmObject->idField => "={$id}"])->first();
    }

    public function findMany(array $findQuery, int $offset = 1, int $limit = 100, array $sort = [])
    {
        $this->result = $this->fmClient->record($this->fmObject->layout)->findRecords($findQuery, $offset, $limit, $sort);

        if (! isset($this->result->data)) {
            return collect();
        }

        return collect($this->result->data)->map(fn (object $dataItem) => $this->fmObject->item($dataItem)->getDataArray());
    }
}
