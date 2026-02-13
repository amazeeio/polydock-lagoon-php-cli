<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\LagoonClientInitializeRequiredToInteractException;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;

class ListGroupsCommand extends LagoonCommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list-groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List groups';

    /**
     * Execute the console command.
     *
     * @throws LagoonClientTokenRequiredToInitializeException|LagoonClientInitializeRequiredToInteractException
     */
    public function handle(): int
    {
        $identity_file = $this->option('identity_file');

        $this->initLagoonClient($identity_file);

        $data = $this->LagoonClient->getAllGroups();

        if (isset($data['error'])) {
            $this->error($data['error'][0]['message']);

            return 1;
        }

        $groupedData = [];
        foreach ($data as $group) {
            $type = empty($group['type']) || $group['type'] === 'null' ? 'standard group' : $group['type'];

            if (! isset($groupedData[$type])) {
                $groupedData[$type] = [];
            }

            $groupedData[$type][] = [
                $group['id'],
                $group['name'],
                $group['organization'],
                $type,
            ];
        }

        // Display tables for each type
        foreach ($groupedData as $type => $tableData) {
            $this->info("\n".ucfirst((string) $type).':');
            $this->table(
                ['ID', 'Name', 'Organization', 'Type'],
                $tableData
            );
        }

        return 0;
    }
}
