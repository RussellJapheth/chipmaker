<?php

namespace Russell\Chipmaker;

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

class App extends CLI
{

    protected function setup(Options $options)
    {
        // Define the apps help
        $options->setHelp(
            'A simple CLI tool to generate CHIP-0007 compatible json files from CSV files.'
        );

        // define the verison option
        $options->registerOption('version', 'print version', 'v');

        // define the file option
        $options->registerOption(
            'file',
            'The path to the source csv file.',
            'f',
            'filename'
        );

        // define the output directory option 
        $options->registerOption(
            'dir',
            'The path to the output directory.',
            'o',
            'outdir'
        );
    }

    protected function main(Options $options)
    {
        // Set the default output directory
        $out_dir = realpath(__DIR__ . '/../output');

        // display the app's version if the version argument is called
        if ($options->getOpt('version')) {
            $this->info('1.0.0');
            return;
        }

        // Parse the options for the output directory
        if ($options->getOpt('dir')) {
            $dir = realpath($options->getOpt('dir'));

            // Check if the path is a directory and is writable
            if (!$dir || !is_dir($dir) || !is_writable($dir)) {
                $this->error(
                    'Unable to access: "' .
                        $options->getOpt('dir') .
                        '". Please make sure the directory exists and is writable.'
                );
                return;
            }

            $out_dir = $dir;
        }

        $this->info("Output directory: " . $out_dir);

        // Parse the options for the csv file
        if ($options->getOpt('file')) {
            $file = realpath($options->getOpt('file'));

            // Check if the path is a file and is readable
            if (!$file || !is_file($file) || !is_readable($file)) {
                $this->error(
                    'Unable to access: "' .
                        $options->getOpt('file') .
                        '". Please make sure the file exists and is readable.'
                );
                return;
            }

            $this->info("Loading csv file: " . $file);

            // run
            $processor = new Processor($file, $out_dir);
            $this->info("Processing...");
            $processor->start();
            $this->info("Done");
            return;
        }

        echo $options->help();
    }
}
