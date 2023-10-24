<?php

namespace Flooris\FileMakerDataApi\RecordRepository;

use stdClass;

abstract class FmBaseObject
{
    use FmBaseObjectValueTrait;

    public int $recordId;
    public int $modId;
    public ?stdClass $portalData;

    /**
     * ToDo: Move this abstract class to the Laravel package
     */
    public function __construct(
        stdClass                 $fmResultObject,
        public ?FmBaseRepository $repository = null,
    )
    {
        $this->recordId   = (int)$fmResultObject->recordId;
        $this->modId      = (int)$fmResultObject->modId;
        $this->fieldData  = $fmResultObject->fieldData;
        $this->portalData = $fmResultObject->portalData;
    }

    public function getPortalArray(string $fieldName): array
    {
        if (! is_array($this->portalData->{$fieldName})) {
            return [];
        }

        return $this->portalData->{$fieldName};
    }
}
