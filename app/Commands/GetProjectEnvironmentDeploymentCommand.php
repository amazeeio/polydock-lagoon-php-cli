<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class GetProjectEnvironmentDeploymentCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-project-environment-deployment {--p|project=} {--e|environment=} {--d|deploymentName=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get project environment deployment';

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

        $deploymentName = $this->option('deploymentName');

        if (empty($deploymentName)) {
            $this->error('Deployment name is required');

            return 1;
        }

        $project = $this->LagoonClient->getProjectByName($project);
        if (! isset($project['projectByName']['id'])) {
            $this->error('Project not found');

            return 1;
        }

        $projectId = $project['projectByName']['id'];
        $deployment = $this->LagoonClient->getProjectDeploymentByProjectIdDeploymentName($projectId, $environment, $deploymentName);

        if (isset($deployment['error'])) {
            $this->error($deployment['error']);

            return 1;
        }

        $tableData = [
            [
                $environment,
                $deployment['id'],
                $deployment['name'],
                $deployment['priority'] ?? '',
                $deployment['buildStep'] ?? '',
                $deployment['status'],
                $deployment['started'] ?? '',
                $deployment['completed'] ?? '',
            ],
        ];

        $this->table(
            ['Environment', 'ID', 'Name', 'Priority', 'Build Step', 'Status', 'Started', 'Completed'],
            $tableData
        );

        return 0;
    }
}
