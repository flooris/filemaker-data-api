<?php

namespace Flooris\FileMakerDataApi\RecordRepository;

use stdClass;

abstract class FmBasePortalObject
{
    use FmBaseObjectValueTrait;

    public int $recordId;

    public int $modId;

    public ?stdClass $fieldData;

    /**
     * ToDo: Move this abstract class to the Laravel package
     */
    public function __construct(
        stdClass $fmResultObject
    ) {
        $this->recordId = (int) $fmResultObject->recordId;
        $this->modId = (int) $fmResultObject->modId;
        $this->fieldData = $fmResultObject;
    }
}
