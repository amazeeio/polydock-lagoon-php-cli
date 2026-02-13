<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class ListAllProjectVariablesCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list-all-project-variables {--p|project=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all project variables over all environments';

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

        $projectVariables = $this->LagoonClient->getProjectVariablesByName($project);

        $tableData = [];
        foreach ($projectVariables as $key => $variable) {
            $tableData[] = [
                $key,
                $variable['value'],
                $variable['scope'],
                '',
            ];
        }

        $this->info('Project-level variables:');
        $headers = ['Name', 'Value', 'Scope', 'Environment'];
        $this->table($headers, $tableData);
        $this->line('');

        $environments = $this->LagoonClient->getProjectEnvironmentsByName($project);

        foreach ($environments as $envName => $environment) {
            if (isset($environment['envVariables'])) {
                $tableData = [];
                foreach ($environment['envVariables'] as $variable) {
                    $tableData[] = [
                        $variable['name'],
                        $variable['value'],
                        $variable['scope'],
                        $envName,
                    ];
                }

                $this->info("Variables for environment '$envName':");
                $this->table($headers, $tableData);
                $this->line('');
            }
        }

        return 0;
    }
}
