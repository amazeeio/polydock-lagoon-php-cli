<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class AddDeployTargetConfigToProjectCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-deploy-target-config-to-project {--p|project=} {--t|deployTargetId=} {--b|branches=} {--w|weight=} {--r|pullrequests=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add deploy target config to project in Lagoon';

    /**
     * Execute the console command.
     *
     * @throws LagoonClientInitializeRequiredToInteractException|LagoonClientTokenRequiredToInitializeException
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

        $deployTargetId = $this->option('deployTargetId');
        if (empty($deployTargetId)) {
            $this->error('Deploy target ID is required');

            return 1;
        }
        $branches = $this->option('branches');
        $pullrequests = $this->option('pullrequests');

        if (empty($branches) || empty($pullrequests)) {
            $this->error('Branches and pullrequests are required');

            return 1;
        }

        $weight = $this->option('weight');
        if (empty($weight)) {
            $weight = 100;
        }

        $data = $this->LagoonClient->addProjectDeployTargetByProjectId($projectId, $deployTargetId, $weight, $branches, $pullrequests);

        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }

        if (! isset($data['addDeployTargetConfig']) || ! isset($data['addDeployTargetConfig']['id'])) {
            $this->error('addDeployTargetConfig ID not returned in data');

            return 1;
        }

        $this->info('Deploy target config added to project successfully: '.$projectName.' ( '.$projectId.' ) to '.$deployTargetId.' ['.$data['addDeployTargetConfig']['id'].']');
        $deployTargetConfigId = $data['addDeployTargetConfig']['id'];

        $deployTargetConfig = $this->LagoonClient->getProjectDeployTargetByConfigId($deployTargetConfigId);

        $this->info('--------------------------------');
        $this->info('Deploy target id: '.$deployTargetConfig['deployTargetConfigById']['id']);
        $this->info('Project: '.$deployTargetConfig['deployTargetConfigById']['project']['name'].' ('.$deployTargetConfig['deployTargetConfigById']['project']['id'].')');
        $this->info('--------------------------------');
        $this->info('Branches: '.$deployTargetConfig['deployTargetConfigById']['branches']);
        $this->info('Pullrequests: '.$deployTargetConfig['deployTargetConfigById']['pullrequests']);
        $this->info('Weight: '.$deployTargetConfig['deployTargetConfigById']['weight']);
        $this->info('--------------------------------');
        $this->info('Deploy target name: '.$deployTargetConfig['deployTargetConfigById']['deployTarget']['name']);
        $this->info('Deploy target friendly name: '.$deployTargetConfig['deployTargetConfigById']['deployTarget']['friendlyName']);
        $this->info('Deploy target cloud region: '.$deployTargetConfig['deployTargetConfigById']['deployTarget']['cloudRegion']);
        $this->info('Deploy target cloud provider: '.$deployTargetConfig['deployTargetConfigById']['deployTarget']['cloudProvider']);

        return 0;
    }
}
