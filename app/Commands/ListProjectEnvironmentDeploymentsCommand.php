<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class ListProjectEnvironmentDeploymentsCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list-project-environment-deployments {--p|project=} {--e|environment=} {--s|status=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List project environment deployments';

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

        $status = $this->option('status');

        $data = $this->LagoonClient->getProjectEnvironmentDeployments($projectName, $environment);

        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }

        $tableData = [];
        foreach ($data as $environment => $deployments) {
            foreach ($deployments as $deployment) {
                if ($status && $deployment['status'] !== $status) {
                    continue;
                }

                $tableData[] = [
                    $environment,
                    $deployment['id'],
                    $deployment['name'],
                    $deployment['priority'] ?? '',
                    $deployment['buildStep'] ?? '',
                    $deployment['status'],
                    $deployment['started'] ?? '',
                    $deployment['completed'] ?? '',
                ];
            }
        }

        $this->table(
            ['Environment', 'ID', 'Name', 'Priority', 'Build Step', 'Status', 'Started', 'Completed'],
            $tableData
        );

        return 0;
    }
}
