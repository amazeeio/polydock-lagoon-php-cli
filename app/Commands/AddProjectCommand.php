<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class AddProjectCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-project {--p|project=} {--g|gitUrl=} {--b|branches=} {--e|productionEnvironment=} {--c|clusterId=} {--k|privateKey=} {--o|organizationId=} {--a|addOrgOwnerToProject=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add project to Lagoon';

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

        $gitUrl = $this->option('gitUrl');
        if (empty($gitUrl)) {
            $this->error('Git URL is required');

            return 1;
        }

        $branches = $this->option('branches');
        if (empty($branches)) {
            $this->error('Branches are required');

            return 1;
        }

        $productionEnvironment = $this->option('productionEnvironment');
        if (empty($productionEnvironment)) {
            $this->error('Production environment is required');

            return 1;
        }

        $clusterId = $this->option('clusterId');
        if (empty($clusterId)) {
            $this->error('Cluster ID is required');

            return 1;
        }

        $privateKey = $this->option('privateKey');
        if ($privateKey && ! file_exists($privateKey)) {
            $this->error('Private key must be a file');

            return 1;
        }

        $privateKeyData = '';
        if ($privateKey) {
            $privateKeyData = file_get_contents($privateKey);
        }

        $organizationId = $this->option('organizationId');
        if (empty($organizationId)) {
            $this->error('Organization ID is required');

            return 1;
        }

        $addOrgOwnerToProject = (bool) $this->option('addOrgOwnerToProject');
        if (empty($addOrgOwnerToProject)) {
            $this->error('Add org owner to project is required');

            return 1;
        }

        $data = $this->LagoonClient->createLagoonProjectInOrganization($projectName, $gitUrl, $branches, $productionEnvironment, $clusterId, $privateKeyData, $organizationId, $addOrgOwnerToProject);

        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }

        $this->info('Project created successfully:');
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $data['addProject']['id']],
                ['Name', $data['addProject']['name']],
                ['Git URL', $data['addProject']['gitUrl']],
                ['Branches', $data['addProject']['branches']],
                ['Production Environment', $data['addProject']['productionEnvironment']],
            ]
        );

        return 0;
    }
}
