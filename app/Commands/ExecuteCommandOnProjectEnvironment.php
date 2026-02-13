<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class ExecuteCommandOnProjectEnvironment extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'execute-command-on-project-environment {--p|project=} {--e|environment=} {--s|service=cli} {--c|container=cli} {execute}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute a command on a project environment';

    /**
     * Execute the console command.
     *
     * @throws LagoonClientTokenRequiredToInitializeException
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
        if (empty($environment)) {
            $this->error('Environment is required');

            return 1;
        }

        $execute = $this->argument('execute');
        if (empty($execute)) {
            $this->error('Command is required');

            return 1;
        }

        $service = $this->option('service');
        $container = $this->option('container');

        $this->info('Executing command in project ['.$project.'] on environment ['.$environment.']');
        $result = $this->LagoonClient->executeCommandOnProjectEnvironment($project, $environment, $execute, $service, $container);

        if (isset($result['command'])) {
            $this->warn('Command: '.$result['command']);
        }

        if (isset($result['result'])) {
            $this->warn('Result: '.$result['result']);
        }

        if (isset($result['result_text'])) {
            $this->warn('Result Text: '.$result['result_text']);
        }

        if (isset($result['output'])) {
            $this->info("Output: \n".$result['output']);
        }

        if (isset($result['error'])) {
            $this->error("Error: \n".$result['error']);
        }

        return 0;
    }
}
