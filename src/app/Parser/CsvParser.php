<?php

namespace App\Parser;

/**
 * Description of CsvParser
 *
 * @author stefan
 */
class CsvParser
{

    protected $file;
    /**
     * First row of file contains headers
     * @var bool
     */
    protected $hasHeaders = true;

    /**
     * Include the headers in the returned data set?
     * [header=>value]
     *
     * $hasHeaders MUST BE TRUE
     *
     * @var bool
     */
    protected $includeHeadersInData = false;

    /**
     *
     * @param string $filePath
     * @param boolean $hasHeaders
     * @param bool|null $includeHeadersInData
     */
    public function __construct(string $filePath, ?bool $hasHeaders = true, ?bool $includeHeadersInData = false)
    {
        $this->file = fopen($filePath, 'r');
        $this->hasHeaders = $hasHeaders;
    }

    public function parse(): ?iterable
    {
        if ($this->hasHeaders) {
            $headers = array_map('trim', (array)fgetcsv($this->file, 4096));
        }

        while (!feof($this->file)) {
            $data = fgetcsv($this->file, 4096);

            if (false === $data) {
                return;
            }

            $row = array_map('trim', (array)$data);

            if ($this->hasHeaders && $this->includeHeadersInData) {
                if (count($headers) !== count($row)) {
                    continue;
                }
                $row = array_combine($headers, $row);
            }

            yield $row;
        }

        $this->rewind();

        return;
    }

    public function rewind(): void
    {
        rewind($this->file);
    }

    public function __destruct()
    {
        fclose($this->file);
    }

}
