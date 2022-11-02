<?php

namespace Russell\Chipmaker;

use League\Csv\Reader;
use League\Csv\Writer;

class Processor
{
    /**
     * The path to the csv file
     *
     * @var string $file
     */
    protected string $file;

    /**
     * The path to the output directory
     *
     * @var string $out_dir
     */
    protected string $out_dir;

    /**
     * An array of records loaded from the csv file
     *
     * @var array $records
     */
    protected array $records = [];

    /**
     * The header row the csv file
     *
     * @var array $header
     */
    protected array $header = [];

    /**
     * The template object loaded from the json file
     *
     * @var object $template
     */
    protected object $template;

    /**
     * The constructor function takes the path to the csv file and output directory
     * 
     * @param string file The path to the file you want to convert.
     * @param string out_dir The directory where the output files will be saved.
     */
    public function __construct(string $file, string $out_dir)
    {
        $this->file = $file;
        $this->out_dir = $out_dir;

        $template = empty(realpath(__DIR__ . '/../template.json')) ?
            realpath(__DIR__ . '/../template.json.example') :
            realpath(__DIR__ . '/../template.json');

        $this->template = json_decode(file_get_contents($template));
    }

    /**
     * Take the data from the CSV file and creates a JSON file for each row in the CSV file
     *
     * @return bool
     */
    private function run()
    {
        $series_total = end($this->records)['Series Number'];
        reset($this->records);

        foreach ($this->records as $key => $row) {
            $entry = $this->template;

            $entry->name = $row['Name'];
            $entry->description = $row['Description'];
            $entry->series_number = (int)$row['Series Number'];
            $entry->series_total = (int)$series_total;

            file_put_contents(
                $this->out_dir . '/' . $row['Filename'] . '.json',
                json_encode($entry, JSON_PRETTY_PRINT)
            );

            $json_sha256 = hash_file(
                'sha256',
                $this->out_dir . '/' . $row['Filename'] . '.json'
            );

            $this->records[$key]['json_sha256'] = $json_sha256;
        }

        $csv = Writer::createFromString();

        //insert the header
        $csv->insertOne([...$this->header, 'json_sha256']);

        //insert all the records
        $csv->insertAll($this->records);

        $output_file = basename($this->file, '.csv');

        return (bool)file_put_contents(
            $this->out_dir . '/' . $output_file . '.output.csv',
            $csv->toString()
        );
    }

    /**
     * It reads the CSV file, gets the header and records, and then runs the `run()` function
     * 
     * @return bool The return value is the result of the run() method.
     */
    public function start()
    {
        $csv = Reader::createFromPath($this->file, 'r');
        $csv->setHeaderOffset(0);

        $this->header = $csv->getHeader();
        $this->records = iterator_to_array($csv->getRecords());

        if (empty($this->records)) {
            return false;
        }
        return $this->run();
    }
}
