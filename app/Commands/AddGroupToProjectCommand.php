<?php

namespace App\Commands;

class AddGroupToProjectCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-group-to-project {--p|project=} {--g|group=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add group to project in Lagoon';

    /**
     * Execute the console command.
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

        $groupName = $this->option('group');
        if (empty($groupName)) {
            $this->error('Group name is required');

            return 1;
        }

        $data = $this->LagoonClient->addGroupToProject($groupName, $projectName);

        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }

        if (! isset($data['addGroupsToProject']) || ! isset($data['addGroupsToProject']['id'])) {
            $this->error('addGroupsToProject ID not found in data');

            return 1;
        }

        $this->info('Group added to project successfully: '.$groupName.' to '.$projectName.' ['.$data['addGroupsToProject']['id'].']');

        return 0;
    }
}
