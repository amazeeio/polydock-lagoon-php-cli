<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class DeleteDeployTargetConfigCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-deploy-target-config {--c|deployTargetConfigId=} {--p|project=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove deploy target config in Lagoon';

    /**
     * Execute the console command.
     *
     * @throws LagoonClientTokenRequiredToInitializeException|LagoonClientInitializeRequiredToInteractException
     */
    public function handle(): int
    {
        $identity_file = $this->option('identity_file');

        $this->initLagoonClient($identity_file);

        $deployTargetConfigId = $this->option('deployTargetConfigId');
        if (empty($deployTargetConfigId)) {
            $this->error('Deploy target config ID is required');

            return 1;
        }

        $deployTargetConfig = $this->LagoonClient->getProjectDeployTargetByConfigId($deployTargetConfigId);

        if (empty($deployTargetConfig) || empty($deployTargetConfig['deployTargetConfigById']['id'])) {
            $this->error('Deploy target config not found: '.$deployTargetConfigId);

            return 1;
        }

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

        $data = $this->LagoonClient->deleteProjectDeployTargetByConfigId($deployTargetConfigId, $projectId);

        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }

        print_r($data);

        return 0;
    }
}
