<?php

namespace Flooris\FileMakerDataApi\RecordRepository;

use stdClass;
use Flooris\FileMakerDataApi\Exceptions\FilemakerDataApiConfigInvalidConnectionException;

abstract class FmBasePortalObject
{
    public int $recordId;
    public int $modId;
    public ?stdClass $fieldData;

    /**
     * ToDo: Move this abstract class to the Laravel package
     */

    public function __construct(
        stdClass $fmResultObject
    )
    {
        $this->recordId  = (int)$fmResultObject->recordId;
        $this->modId     = (int)$fmResultObject->modId;
        $this->fieldData = $fmResultObject;
    }

    public function getValue(string $fieldName, bool $nullable = false): mixed
    {
        if ($nullable) {
            return match ($this->fieldData->{$fieldName}) {
                "", null => null,
                0 => 0,
                default => $this->fieldData->{$fieldName}
            };
        }

        return $this->fieldData->{$fieldName};
    }

    public function getValueAsBoolean(string $fieldName): bool
    {
        $value      = strtolower($this->getValue($fieldName));
        $trueValues = config('filemaker.settings.boolean_true_values');

        if (! is_array($trueValues)) {
            throw new FilemakerDataApiConfigInvalidConnectionException("Package config: 'filemaker.settings.boolean_true_values' is invalid");
        }

        return in_array($value, $trueValues);
    }
}
