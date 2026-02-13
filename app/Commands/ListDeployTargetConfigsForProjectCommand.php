<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class ListDeployTargetConfigsForProjectCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list-deploy-target-configs-for-project {--p|project=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List deploy target configs for project in Lagoon';

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

        $project = $this->LagoonClient->getProjectByName($projectName);
        $projectId = empty($project['projectByName']) || empty($project['projectByName']['id']) ? null : $project['projectByName']['id'];

        if (empty($projectId)) {
            $this->error('Project not found: '.$projectName);

            return 1;
        }

        $deployTargetConfigs = $this->LagoonClient->getProjectDeployTargetsByProjectId($projectId);

        if (empty($deployTargetConfigs['deployTargetConfigsByProjectId'])) {
            $this->info('No deploy target configs found for project: '.$projectName);

            return 0;
        }

        $tableData = [];
        foreach ($deployTargetConfigs['deployTargetConfigsByProjectId'] as $config) {
            $tableData[] = [
                'ID' => $config['id'],
                'Branches' => $config['branches'],
                'Pull Requests' => $config['pullrequests'] ?: '',
                'Weight' => $config['weight'] ?: '',
                'Deploy Target' => $config['deployTarget']['name'].' ('.$config['deployTarget']['id'].')',
                'Friendly Name' => $config['deployTarget']['friendlyName'] ?: 'N/A',
                'Cloud Region' => $config['deployTarget']['cloudRegion'] ?: 'N/A',
                'Cloud Provider' => $config['deployTarget']['cloudProvider'] ?: 'N/A',
            ];
        }

        $this->table([
            'ID',
            'Branches',
            'Pull Requests',
            'Weight',
            'Deploy Target',
            'Friendly Name',
            'Cloud Region',
            'Cloud Provider',
        ], $tableData);

        $this->info('Found '.count($tableData).' deploy target config(s) for project: '.$projectName);

        return 0;
    }
}
