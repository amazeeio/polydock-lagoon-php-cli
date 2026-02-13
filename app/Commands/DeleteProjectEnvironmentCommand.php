<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class DeleteProjectEnvironmentCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-project-environment {--p|project=} {--e|environment=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a project environment';

    /**
     * Execute the console command.
     *
     * @throws LagoonClientTokenRequiredToInitializeException|LagoonClientInitializeRequiredToInteractException
     */
    public function handle(): int
    {
        $identity_file = $this->option('identity_file');

        $this->initLagoonClient($identity_file);

        $projectName = $this->option('project');
        if (empty($projectName)) {
            $this->error('Project name is required');

            return 1;
        }

        $environmentName = $this->option('environment');
        if (empty($environmentName)) {
            $this->error('Environment name is required');

            return 1;
        }

        if (! $this->confirm(
            "Are you sure you want to delete environment '$environmentName' for project '$projectName'? This action cannot be reversed."
        )) {
            $this->info('Operation cancelled');

            return 0;
        }

        $data = $this->LagoonClient->deleteProjectEnvironmentByName(
            $projectName,
            $environmentName
        );

        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }

        if (isset($data['deleteEnvironment']) && $data['deleteEnvironment'] === 'success') {
            $this->info('Environment deleted successfully');
        } else {
            $this->error('Failed to delete environment');

            return 1;
        }

        return 0;
    }
}
