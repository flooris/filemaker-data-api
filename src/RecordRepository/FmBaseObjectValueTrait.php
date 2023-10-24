<?php

namespace Flooris\FileMakerDataApi\RecordRepository;

use Flooris\FileMakerDataApi\Exceptions\FilemakerDataApiConfigInvalidConnectionException;

trait FmBaseObjectValueTrait
{
    public ?\stdClass $fieldData;

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
     * @throws FilemakerDataApiConfigInvalidConnectionException
     */
    public function getValueAsBoolean(string $fieldName): bool
    {
        $value      = strtolower($this->getValue($fieldName));
        $trueValues = config('filemaker.settings.boolean_true_values');

        if (! is_array($trueValues)) {
            throw new FilemakerDataApiConfigInvalidConnectionException("Package config: 'filemaker.settings.boolean_true_values' is invalid");
        }

        return in_array($value, $trueValues);
    }

    public function getValueAsFloat(string $fieldName): float
    {
        $value = strtolower($this->getValue($fieldName));

        if (is_float($value)) {
            return $value;
        }

        if (stristr($value, ',') && stristr($value, '.') === false) {
            return (float)str_replace(',', '.', $value);
        }

        return (float)$value;
    }
}