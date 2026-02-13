<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class ListProjectVariablesCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list-project-variables {--p|project=} {--e|environment=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List project (and potentially environment) variables';

    /**
     * Execute the console command.
     *
     * @throws LagoonClientTokenRequiredToInitializeException|LagoonClientInitializeRequiredToInteractException
     */
    public function handle(): int
    {
        $identity_file = $this->option('identity_file');

        $project = $this->option('project');

        if (empty($project)) {
            $this->error('Project is required');

            return 1;
        }

        $environment = $this->option('environment');

        $this->initLagoonClient($identity_file);

        if ($environment) {
            $data = $this->LagoonClient->getProjectVariablesByNameForEnvironment($project, $environment);
        } else {
            $data = $this->LagoonClient->getProjectVariablesByName($project);
        }

        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }

        $tableData = [];
        foreach ($data as $key => $variable) {
            $tableData[] = [
                $key,
                $variable['value'],
                $variable['scope'],
                $environment,
            ];
        }

        $headers = ['Name', 'Value', 'Scope', 'Environment'];
        if ($environment) {
            $this->info("Variables for project '$project' in environment '$environment':");
        } else {
            $this->info("Variables for project '$project':");
        }

        $this->table($headers, $tableData);

        return 0;
    }
}
