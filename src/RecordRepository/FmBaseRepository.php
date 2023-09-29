<?php

namespace Flooris\FileMakerDataApi\RecordRepository;

use stdClass;
use Exception;
use Flooris\FileMakerDataApi\Client;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\SimpleCache\InvalidArgumentException;

abstract class FmBaseRepository
{
    public bool $findRequestFailed = false;
    public ?Exception $lastException = null;
    public int $totalRecordCount = 0;
    public int $foundCount = 0;
    public int $currentResultCount = 0;
    public int $recordPointer = 0;

    private array $findQueryAll;

    /**
     * ToDo: Move this abstract class to the Laravel package
     * ToDo: Add tests
     * ToDo: Add custom specific Exceptions for common cases
     */

    public function __construct(
        private Client $fmClient,
        public string  $fmLayoutName,
        public string  $fmIdFieldName
    )
    {
        $this->setFindQueryAll([
            $this->fmIdFieldName => '>0',
        ]);
    }

    public function findRecordById(int $id): ?stdClass
    {
        try {
            $fmDataRecords = $this->findRecords($this->getFindQueryById($id));
            if (! $fmDataRecords || ! is_array($fmDataRecords)) {
                return null;
            }

            return reset($fmDataRecords);
        } catch (InvalidArgumentException $exception) {
            // ToDo: Handle exception
        }

        return null;
    }

    public function findRecords(array $findQuery, int $offset = 1, int $limit = 100, array $sort = []): ?array
    {
        try {
            $result = $this
                ->fmClient
                ->record($this->fmLayoutName)
                ->findRecords($findQuery, $offset, $limit, $sort);
        } catch (Exception|InvalidArgumentException $exception) {
            $this->findRequestFailed = true;
            $this->lastException     = $exception;

            return null;
        }

        if (! $result ||
            ! isset($result->data) ||
            ! isset($result->dataInfo)
        ) {
            return null;
        }

        $this->totalRecordCount   = $result->dataInfo->totalRecordCount;
        $this->foundCount         = $result->dataInfo->foundCount;
        $this->currentResultCount += $result->dataInfo->returnedCount;

        return $result->data;
    }

    public function each(callable $callback): void
    {
        $isFirstPage = true;
        $offset      = 1;
        $limit       = 100;

        $this->recordPointer      = 0;
        $this->currentResultCount = 0;

        while ($isFirstPage || $this->hasMoreRecords()) {
            if ($isFirstPage) {
                $isFirstPage = false;
            } else {
                $offset += $limit;
            }

            $fmDataRecords = $this->findRecords($this->getFindQueryAll(), $offset, $limit);

            if (! $fmDataRecords) {
                return;
            }

            foreach ($fmDataRecords as $fmDataRecord) {
                $this->recordPointer++;

                $callback($fmDataRecord);
            }
        }
    }

    private function getFindQueryById(int $id): array
    {
        return [
            $this->fmIdFieldName => "={$id}",
        ];
    }

    public function setFindQueryAll(array $findQuery): self
    {
        $this->findQueryAll = $findQuery;

        return $this;
    }

    public function getTotalRecordCount(): int
    {
        $this->findRecords($this->getFindQueryAll(), 1, 1);

        return $this->totalRecordCount;
    }

    public function getDataContainerToken(string $dataContainerObjectUrl): ?string
    {
        return $this->fmClient->getDataContainerToken($dataContainerObjectUrl);
    }

    /**
     * @throws GuzzleException
     */
    public function getDataContainerContent(string $dataContainerObjectUrl, string $dataContainerToken): ?StreamInterface
    {
        return $this->fmClient->getDataContainerContent($dataContainerObjectUrl, $dataContainerToken);
    }


    private function getFindQueryAll(): array
    {
        return $this->findQueryAll;
    }

    private function hasMoreRecords(): bool
    {
        return ($this->totalRecordCount > $this->currentResultCount);
    }
}
