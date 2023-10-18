<?php

namespace Flooris\FileMakerDataApi\Commands;

use Illuminate\Console\Command;

class FileMakerDataApiCommand extends Command
{
    public $signature = 'filemaker-data-api';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
