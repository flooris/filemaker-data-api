<?php

namespace Flooris\FileMakerDataApi\RecordRepository;

use Flooris\FileMakerDataApi\Exceptions\FilemakerDataApiConfigInvalidException;

abstract class FmBaseObject
{
    public int $recordId;
    public int $modId;
    public ?\stdClass $fieldData;
    public ?\stdClass $portalData;

    /**
     * ToDo: Move this abstract class to the Laravel package
     */

    public function __construct(
        \stdClass                $fmResultObject,
        public ?FmBaseRepository $repository = null,
    )
    {
        $this->recordId   = (int)$fmResultObject->recordId;
        $this->modId      = (int)$fmResultObject->modId;
        $this->fieldData  = $fmResultObject->fieldData;
        $this->portalData = $fmResultObject->portalData;
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

    /**
     * @throws FilemakerDataApiConfigInvalidException
     */
    public function getValueAsBoolean(string $fieldName): bool
    {
        $value = strtolower($this->getValue($fieldName));

        // ToDo: Get match values from Laravel package config file (only the boolean: true values)
        $trueValues = config('filemaker.settings.boolean_true_values');
        if (! is_array($trueValues)) {
            throw new FilemakerDataApiConfigInvalidException("Package config: 'filemaker.settings.boolean_true_values' is invalid");
        }

        return match ($value) {
            'true', '1', 'yes', 'y', 'j', 'ja' => true,
            default => false
        };
    }

    public function getPortalArray(string $fieldName): array
    {
        if (! is_array($this->portalData->{$fieldName})) {
            return [];
        }

        return $this->portalData->{$fieldName};
    }
}
