<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class LoginCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'login';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Login to Lagoon';

    /**
     * Execute the console command.
     *
     * @throws LagoonClientTokenRequiredToInitializeException|LagoonClientInitializeRequiredToInteractException
     */
    public function handle(): void
    {
        $identity_file = $this->option('identity_file');
        $this->info('Using identity file: '.$identity_file);

        $this->initLagoonClient($identity_file);

        $data = $this->LagoonClient->whoAmI();

        $this->table(
            [],
            [
                ['Lagoon Version', $data['lagoonVersion']],
                ['Email', $data['me']['email']],
            ]
        );
    }
}
