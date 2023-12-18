<?php

namespace Flooris\FileMakerDataApi\RecordRepository;

abstract class FmObject
{
    public function __construct(
        public readonly string $layout,
        public readonly string $idField,
        public readonly string $fmBaseObjectClass,
    ) {
    }

    public function item(object $result): FmBaseObject
    {
        return new $this->fmBaseObjectClass($result);
    }
}
