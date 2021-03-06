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
* PHP Mess Detector Plugin - Allows PHP Mess Detector testing.
* @author       Dan Cryer <dan@block8.co.uk>
* @package      PHPCI
* @subpackage   Plugins
*/
class PhpMessDetector implements \PHPCI\Plugin
{
    protected $directory;

    public function __construct(\PHPCI\Builder $phpci, array $options = array())
    {
        $this->phpci        = $phpci;
    }

    /**
    * Runs PHP Mess Detector in a specified directory.
    */
    public function execute()
    {
        $ignore = '';
        
        if (count($this->phpci->ignore)) {
            $ignore = ' --exclude ' . implode(',', $this->phpci->ignore);
        }

        $cmd = PHPCI_BIN_DIR . 'phpmd "%s" text codesize,unusedcode,naming %s';
        return $this->phpci->executeCommand($cmd, $this->phpci->buildPath, $ignore);
    }
}
