<?php

namespace App\Commands;

class GetProjectVariableCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-project-variable {--p|project=} {--e|environment=} {--k|key=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get project variable';

    /**
     * Execute the console command.
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

        $environment = $this->option('environment');

        $key = $this->option('key');

        if (empty($key)) {
            $this->error('Variable name (Key) is required');

            return 1;
        }

        if ($environment) {
            $variableData = $this->LagoonClient->getProjectVariableByNameForEnvironment($project, $environment, $key);
        } else {
            $variableData = $this->LagoonClient->getProjectVariableByName($project, $key);
        }

        if (! isset($variableData['value'])) {
            $this->error('Variable not found');

            return 1;
        }

        $tableData = [
            [
                $key,
                $variableData['value'],
                $variableData['scope'],
                $environment,
            ],
        ];

        $headers = ['Name', 'Value', 'Scope', 'Environment'];

        $this->table($headers, $tableData);

        return 0;
    }
}
