<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;
use FreedomtechHosting\FtLagoonPhp\LagoonVariableScopeInvalidException;

class AddOrUpdateProjectVariable extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-project-variable {--p|project=} {--e|environment=} {--k|key=} {--a|value=} {--s|scope=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add or update project variable';

    /**
     * Execute the console command.
     *
     * @throws LagoonClientTokenRequiredToInitializeException
     * @throws LagoonClientInitializeRequiredToInteractException
     * @throws LagoonVariableScopeInvalidException
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

        $environment = $this->option('environment');

        $key = $this->option('key');
        if (empty($key)) {
            $this->error('Key is required');

            return 1;
        }

        $value = $this->option('value');
        if (empty($value)) {
            $this->error('Value is required');

            return 1;
        }

        $scope = $this->option('scope');
        if (empty($scope)) {
            $this->error('Scope is required');

            return 1;
        }

        $validScopes = ['GLOBAL', 'RUNTIME', 'BUILD', 'CONTAINER_REGISTRY'];
        if (! in_array(strtoupper($scope), $validScopes)) {
            $this->error('Invalid scope. Must be one of: '.implode(', ', $validScopes));

            return 1;
        }

        if ($environment) {
            $data = $this->LagoonClient->addOrUpdateScopedVariableForProjectEnvironment(
                $projectName,
                $environment,
                $key,
                $value,
                $scope
            );
        } else {
            $data = $this->LagoonClient->addOrUpdateScopedVariableForProject(
                $projectName,
                $key,
                $value,
                $scope
            );
        }

        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }

        if (isset($data['addOrUpdateEnvVariableByName'])) {
            $this->info('Variable added/updated successfully:');
            $this->table(
                ['Field', 'Value'],
                array_filter([
                    ['ID', $data['addOrUpdateEnvVariableByName']['id']],
                    ['Name', $data['addOrUpdateEnvVariableByName']['name']],
                    ['Value', $data['addOrUpdateEnvVariableByName']['value']],
                    ['Scope', $data['addOrUpdateEnvVariableByName']['scope']],
                    $environment ? ['Environment', $environment] : null,
                ])
            );
        } else {
            $this->info('Variable is already present');
        }

        return 0;
    }
}
