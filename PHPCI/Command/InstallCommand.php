<?php
/**
* PHPCI - Continuous Integration for PHP
*
* @copyright    Copyright 2013, Block 8 Limited.
* @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
* @link         http://www.phptesting.org/
*/

namespace PHPCI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use b8\Store\Factory;
use PHPCI\Builder;

/**
* Install console command - Installs PHPCI.
* @author       Dan Cryer <dan@block8.co.uk>
* @package      PHPCI
* @subpackage   Console
*/
class InstallCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('phpci:install')
            ->setDescription('Install PHPCI.');
    }

    /**
    * Installs PHPCI - Can be run more than once as long as you ^C instead of entering an email address.
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Gather initial data from the user:
        $dbHost = $this->ask('Enter your MySQL host: ');
        $dbName = $this->ask('Enter the database name PHPCI should use: ');
        $dbUser = $this->ask('Enter your MySQL username: ');
        $dbPass = $this->ask('Enter your MySQL password: ', true);
        $ciUrl = $this->ask('Your PHPCI URL (without trailing slash): ');
        $ghId = $this->ask('(Optional) Github Application ID: ', true);
        $ghSecret = $this->ask('(Optional) Github Application Secret: ', true);

        // Create the database if it doesn't exist:
        $cmd    = 'mysql -u' . $dbUser . (!empty($dbPass) ? ' -p' . $dbPass : '') . ' -h' . $dbHost .
                    ' -e "CREATE DATABASE IF NOT EXISTS ' . $dbName . '"';

        shell_exec($cmd);

        $str = "<?php

if(!defined('PHPCI_DB_HOST')) {
    define('PHPCI_DB_HOST', '{$dbHost}');
}

b8\Database::setDetails('{$dbName}', '{$dbUser}', '{$dbPass}');
b8\Database::setWriteServers(array('{$dbHost}'));
b8\Database::setReadServers(array('{$dbHost}'));

\$config = b8\Config::getInstance();
\$config->set('install_url', '{$ciUrl}');
";

        // Has the user entered Github app details? If so add those to config:
        if (!empty($ghId) && !empty($ghSecret)) {
            $str .= PHP_EOL .
                    "\$registry->set('github_app', array('id' => '{$ghId}', 'secret' => '{$ghSecret}'));" .
                    PHP_EOL;
        }

        // Write the config file and then re-bootstrap:
        file_put_contents(PHPCI_DIR . 'config.php', $str);
        require(PHPCI_DIR . 'bootstrap.php');

        // Update the database:
        $gen = new \b8\Database\Generator(\b8\Database::getConnection(), 'PHPCI', './PHPCI/Model/Base/');
        $gen->generate();

        // Try to create a user account:
        $adminEmail = $this->ask('Enter your email address (leave blank if updating): ', true);

        if (empty($adminEmail)) {
            return;
        }
        
        $adminPass = $this->ask('Enter your desired admin password: ');
        $adminName = $this->ask('Enter your name: ');

        try {
            $user = new \PHPCI\Model\User();
            $user->setEmail($adminEmail);
            $user->setName($adminName);
            $user->setIsAdmin(1);
            $user->setHash(password_hash($adminPass, PASSWORD_DEFAULT));

            $store = \b8\Store\Factory::getStore('User');
            $store->save($user);

            print 'User account created!' . PHP_EOL;
        } catch (\Exception $ex) {
            print 'There was a problem creating your account. :(' . PHP_EOL;
            print $ex->getMessage();
        }
    }

    protected function ask($question, $emptyOk = false)
    {
        print $question . ' ';

        $rtn    = '';
        $stdin     = fopen('php://stdin', 'r');
        $rtn = fgets($stdin);
        fclose($stdin);

        $rtn = trim($rtn);

        if (!$emptyOk && empty($rtn)) {
            $rtn = $this->ask($question, $emptyOk);
        }

        return $rtn;
    }
}
