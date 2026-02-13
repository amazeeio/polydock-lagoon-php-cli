<?php

declare(strict_types=1);

return [
    'ssh_private_key_file' => env('FTLAGOON_PRIVATE_KEY_FILE', '~/.ssh/id_rsa'),
    'ssh_user' => env('FTLAGOON_SSH_USER', 'lagoon'),
    'ssh_server' => env('FTLAGOON_SSH_SERVER', 'ssh.lagoon.amazeeio.cloud'),
    'ssh_port' => env('FTLAGOON_SSH_PORT', '32222'),
    'endpoint' => env('FTLAGOON_ENDPOINT', 'https://api.lagoon.amazeeio.cloud/graphql'),
];
