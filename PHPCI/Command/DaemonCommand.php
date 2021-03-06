<?php
/**
* PHPCI - Continuous Integration for PHP
* nohup PHPCI_DIR/console phpci:start-daemon > /dev/null 2>&1 &
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
use PHPCI\BuildFactory;

/**
* Daemon that loops and call the run-command.
* @author       Gabriel Baker <gabriel.baker@autonomicpilot.co.uk>
* @package      PHPCI
* @subpackage   Console
*/
class DaemonCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('phpci:daemon')
            ->setDescription('Initiates the daemon to run commands.')
            ->addArgument(
                'state',
                InputArgument::REQUIRED,
                'start|stop|status'
            );
    }

    /**
    * Loops through running.
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $state = $input->getArgument('state');

        switch ($state) {
            case 'start':
                $this->startDaemon();
                break;
            case 'stop':
                $this->stopDaemon();
                break;
            case 'status':
                $this->statusDaemon();
                break;
            default:
                echo "Not a valid choice, please use start stop or status";
                break;
        }

    }

    protected function startDaemon()
    {

        if ( file_exists(PHPCI_DIR.'/daemon/daemon.pid') ) {
            echo "Already started\n";
            return "alreadystarted";
        }

        $logfile = PHPCI_DIR."/daemon/daemon.log";
        $cmd = "nohup %s/daemonise phpci:daemonise > %s 2>&1 &";
        $command = sprintf($cmd, PHPCI_DIR, $logfile);
        exec($command);
    }

    protected function stopDaemon()
    {

        if ( !file_exists(PHPCI_DIR.'/daemon/daemon.pid') ) {
            echo "Not started\n";
            return "notstarted";
        }

        $cmd = "kill $(cat %s/daemon/daemon.pid)";
        $command = sprintf($cmd, PHPCI_DIR);
        exec($command);
        unlink(PHPCI_DIR.'/daemon/daemon.pid');
    }

    protected function statusDaemon()
    {

        if ( !file_exists(PHPCI_DIR.'/daemon/daemon.pid') ) {
            echo "Not running\n";
            return "notrunning";
        }

        $pid = trim(file_get_contents(PHPCI_DIR.'/daemon/daemon.pid'));
        $pidcheck = sprintf("/proc/%s", $pid);
        if ( is_dir($pidcheck) ) {
            echo "Running\n";
            return "running";
        }

        unlink(PHPCI_DIR.'/daemon/daemon.pid');
        echo "Not running\n";
        return "notrunning";
    }

    /**
    * Called when log entries are made in Builder / the plugins.
    * @see \PHPCI\Builder::log()
    */
    public function logCallback($log)
    {
        $this->output->writeln($log);
    }
}
