<?php

namespace Homeful\Borrower\Commands;

use Illuminate\Console\Command;

class BorrowerCommand extends Command
{
    public $signature = 'borrower';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
