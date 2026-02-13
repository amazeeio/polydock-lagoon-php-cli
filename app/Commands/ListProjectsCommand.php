<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class ListProjectsCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list-projects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List projects';

    /**
     * Execute the console command.
     *
     * @throws LagoonClientTokenRequiredToInitializeException|LagoonClientInitializeRequiredToInteractException
     */
    public function handle(): int
    {
        $identity_file = $this->option('identity_file');

        $this->initLagoonClient($identity_file);

        $data = $this->LagoonClient->getAllProjects();

        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }

        $tableData = [];
        foreach ($data['allProjects'] as $project) {
            $tableData[] = [
                $project['id'],
                $project['name'],
                $project['productionEnvironment'],
                $project['branches'],
                $project['gitUrl'],
                $project['openshift']['name'],
                $project['openshift']['cloudProvider'],
                $project['openshift']['cloudRegion'],
                $project['created'],
                $project['availability'],
            ];
        }

        $this->table(
            ['ID', 'Name', 'Prod Env', 'Branches', 'Git URL', 'Cluster', 'Cloud Provider', 'Region', 'Created', 'Availability'],
            $tableData
        );

        return 0;
    }
}
