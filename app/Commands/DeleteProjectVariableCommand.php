<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class DeleteProjectVariableCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-project-variable {--p|project=} {--e|environment=} {--k|key=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete project variable';

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

        $environment = $this->option('environment');

        $key = $this->option('key');
        if (empty($key)) {
            $this->error('Key is required');

            return 1;
        }

        if (! $this->confirm(
            $environment
                ? "Are you sure you want to delete variable '$key' from environment '$environment' in project '$projectName'? This action cannot be reversed."
                : "Are you sure you want to delete variable '$key' from project '$projectName'? This action cannot be reversed."
        )) {
            $this->info('Operation cancelled');

            return 0;
        }

        if ($environment) {
            $data = $this->LagoonClient->deleteProjectVariableByNameForEnvironment(
                $projectName,
                $key,
                $environment
            );
        } else {
            $data = $this->LagoonClient->deleteProjectVariableByName(
                $projectName,
                $key
            );
        }

        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }

        if (isset($data['deleteEnvVariableByName']) && $data['deleteEnvVariableByName'] === 'success') {
            $this->info('Variable deleted successfully');
        } else {
            $this->error('Failed to delete variable');

            return 1;
        }

        return 0;
    }
}
