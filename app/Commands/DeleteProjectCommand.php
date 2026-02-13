<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class DeleteProjectCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-project {--p|project=} {--r|recursive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a project';

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

        $recursive = $this->option('recursive');

        if (! $this->confirm(
            $recursive
                ? "Are you sure you want to delete project '$projectName' and all its environments? This action cannot be reversed."
                : "Are you sure you want to delete project '$projectName'? This action cannot be reversed."
        )) {
            $this->info('Operation cancelled');

            return 0;
        }

        if ($recursive) {
            $environments = $this->LagoonClient->getProjectEnvironmentsByName($projectName);

            if (isset($environments['error'])) {
                $this->error($environments['error'][0]['message']);

                return 1;
            }

            foreach ($environments as $environment) {
                $envData = $this->LagoonClient->deleteProjectEnvironmentByName(
                    $projectName,
                    $environment['name']
                );

                if (isset($envData['error'])) {
                    $this->error($envData['error'][0]['message']);

                    return 1;
                }

                if (isset($envData['deleteEnvironment']) && $envData['deleteEnvironment'] === 'success') {
                    $this->info("Environment {$environment['name']} deleted successfully");
                } else {
                    $this->error("Failed to delete environment {$environment['name']}");

                    return 1;
                }
            }
        }

        $data = $this->LagoonClient->deleteProjectByName(
            $projectName,
        );

        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }
        if (isset($data['deleteProject']) && $data['deleteProject'] === 'success') {
            $this->info('Project deleted successfully');
        } else {
            $this->error('Failed to delete project');

            return 1;
        }

        print_r($data);

        return 0;
    }
}
