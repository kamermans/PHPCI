<?php
/**
* PHPCI - Continuous Integration for PHP
*
* @copyright    Copyright 2013, Block 8 Limited.
* @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
* @link         http://www.phptesting.org/
*/

namespace PHPCI\Plugin;

/**
* Clean build removes Composer related files and allows PHPCI users to clean up their build directory.
* Useful as a precursor to copy_build.
* @author       Dan Cryer <dan@block8.co.uk>
* @package      PHPCI
* @subpackage   Plugins
*/
class CleanBuild implements \PHPCI\Plugin
{
    protected $remove;
    protected $phpci;

    public function __construct(\PHPCI\Builder $phpci, array $options = array())
    {
        $path               = $phpci->buildPath;
        $this->phpci        = $phpci;
        $this->remove       = isset($options['remove']) && is_array($options['remove']) ? $options['remove'] : array();
    }

    /**
    * Executes Composer and runs a specified command (e.g. install / update)
    */
    public function execute()
    {
        $cmd = 'rm -Rf "%s"';
        $this->phpci->executeCommand($cmd, $this->phpci->buildPath . 'composer.phar');
        $this->phpci->executeCommand($cmd, $this->phpci->buildPath . 'composer.lock');

        $success = true;
        
        foreach ($this->remove as $file) {
            $ok = $this->phpci->executeCommand($cmd, $this->phpci->buildPath . $file);

            if (!$ok) {
                $success = false;
            }
        }

        return $success;
    }
}
