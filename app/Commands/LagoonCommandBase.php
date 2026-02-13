<?php

namespace App\Commands;

use FreedomtechHosting\FtLagoonPhp\Client;
use FreedomtechHosting\FtLagoonPhp\LagoonClientTokenRequiredToInitializeException;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Base command class for Lagoon CLI commands
 *
 * Provides common functionality for authenticating and interacting with the Lagoon API
 */
abstract class LagoonCommandBase extends Command
{
    /** @var Client The Lagoon API client instance */
    protected Client $LagoonClient;

    /** @var string The application directory path for storing tokens */
    protected string $APPDIR;

    /** @var int Maximum age in minutes before a token is considered expired */
    const int MAX_TOKEN_AGE_MINUTES = 5;

    /** @var string Default ssh id file */
    const string DEFAULT_IDENTITY_FILE = '~/.ssh/id_rsa';

    /**
     * Configure the command options across all children
     *
     * Adds an option for specifying the ssh identity file
     */
    protected function configure(): void
    {
        parent::configure();
        $this->getDefinition()->addOption(
            new InputOption(
                'identity_file',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Path to your SSH identity file (default: '.self::DEFAULT_IDENTITY_FILE.')',
            )
        );
    }

    /**
     * Constructor
     *
     * Sets up the application directory for storing authentication tokens
     */
    public function __construct()
    {
        $HOME = getenv('HOME') ?? '/tmp/';
        $this->APPDIR = $HOME.DIRECTORY_SEPARATOR.'.ftlagoonphp';

        if (! is_dir($this->APPDIR)) {
            mkdir($this->APPDIR);
        }

        parent::__construct();
    }

    /**
     * Initialize the Lagoon API client
     *
     * Sets up authentication using an SSH key and manages token caching
     *
     * @param  string  $sshPrivateKeyFile  Path to SSH private key file
     *
     * @throws LagoonClientTokenRequiredToInitializeException
     */
    protected function initLagoonClient(string $sshPrivateKeyFile): void
    {

        $clientOptions = [];

        if (! empty($sshPrivateKeyFile)) {
            $HOME = getenv('HOME') ?? '/tmp/';

            if (str_starts_with($sshPrivateKeyFile, '~')) {
                $sshPrivateKeyFile = $HOME.substr($sshPrivateKeyFile, 1);
            }
            // Debugging statement removed to prevent unintended output in production.
            $clientOptions['ssh_private_key_file'] = $sshPrivateKeyFile;
        } else {
            $sshPrivateKeyFile = 'UNSET'; // we set this to a string so we can use it in the token file name
        }

        $this->LagoonClient = app(Client::class, $clientOptions);

        $tokenFile = $this->APPDIR.DIRECTORY_SEPARATOR.md5($sshPrivateKeyFile).'.token';

        if (file_exists($tokenFile) && ! (((time() - filemtime($tokenFile)) / 60) > self::MAX_TOKEN_AGE_MINUTES)) {
            $this->info('Loaded token from: '.$tokenFile);
            $this->LagoonClient->setLagoonToken(file_get_contents($tokenFile));
        } else {
            $this->LagoonClient->getLagoonTokenOverSsh();

            if ($this->LagoonClient->getLagoonToken()) {
                $this->info('Saved token to: '.$tokenFile);
                file_put_contents($tokenFile, $this->LagoonClient->getLagoonToken());
            } else {
                $this->error('Could not load a Laoon token');
            }
        }

        $this->LagoonClient->initGraphqlClient();
    }
}
