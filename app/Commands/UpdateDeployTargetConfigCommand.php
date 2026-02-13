<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class UpdateDeployTargetConfigCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-deploy-target-config {--c|deployTargetConfigId=} {--t|deployTargetId=} {--w|weight=}  {--b|branches=} {--r|pullrequests=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update deploy target config in Lagoon';

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

        $data = $this->LagoonClient->updateProjectDeployTargetByConfigId($deployTargetConfigId, $deployTargetId, $weight, $branches, $pullrequests);

        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }

        if (! isset($data['updateDeployTargetConfig']) || ! isset($data['updateDeployTargetConfig']['id'])) {
            $this->error('updateDeployTargetConfig ID not returned in data');

            return 1;
        }

        $this->info('Deploy target config updated successfully: '.$deployTargetConfigId.' ['.$data['updateDeployTargetConfig']['id'].']');
        $deployTargetConfigId = $data['updateDeployTargetConfig']['id'];

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
