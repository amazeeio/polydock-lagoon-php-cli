<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class DeployProjectEnvironmentCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy-project-environment {--p|project=} {--e|environment=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy a project branch';

    /**
     * Execute the console command.
     *
     * @throws LagoonClientTokenRequiredToInitializeException|LagoonClientInitializeRequiredToInteractException
     */
    public function handle(): int
    {
        $identity_file = $this->option('identity_file');
        $this->initLagoonClient($identity_file);

        $project = $this->option('project');
        if (empty($project)) {
            $this->error('Project is required');

            return 1;
        }

        $environment = $this->option('environment');
        if (empty($environment)) {
            $this->error('Environment is required');

            return 1;
        }

        $data = $this->LagoonClient->deployProjectEnvironmentByName($project, $environment);
        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }

        $this->info('Deployment initiated with build ID: '.$data['deployEnvironmentBranch']);

        return 0;
    }
}
