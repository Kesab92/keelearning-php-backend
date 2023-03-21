<?php

namespace App\Imports;

use App\Imports\Exceptions\InvalidDataException;
use App\Imports\Exceptions\InvalidHeadersException;
use App\Models\Import;

abstract class Importer
{
    /**
     * Defines the headers which need to be present.
     * @var null
     */
    protected $necessaryHeaders = null;

    /**
     * @var Import
     */
    protected $import;

    private $lastProgressUpdate;

    /**
     * Imports the given questions after validating the input.
     *
     * @param $additionalData
     * @param $headers
     * @param $data
     * @throws \Exception
     */
    public function import($additionalData, $headers, $data)
    {
        set_time_limit(0);
        ini_set('memory_limit', '4G');

        $this->validateHeaders($headers);

        $this->validateData($data);

        $this->importData($additionalData, $headers, $data);
    }

    /**
     * Collects information about what is going to be created or updated.
     *
     * @param $additionalData
     * @param $headers
     * @param $users
     * @throws \Exception
     */
    public function collectChanges($additionalData, $headers, $data)
    {
        throw new \Exception('Data collection has not been defined');
    }

    /**
     * Checks if the given headers are valid.
     *
     * @param $headers
     * @throws \Exception
     */
    protected function validateHeaders($headers)
    {
        if ($this->necessaryHeaders === null) {
            throw new \Exception('Necessary headers have not been defined');
        }
        foreach ($this->necessaryHeaders as $necessaryHeader) {
            if (! in_array($necessaryHeader, $headers)) {
                throw new InvalidHeadersException();
            }
        }
    }

    /**
     * Checks if all data lines can be valid.
     *
     * @param $data
     * @throws \Exception
     */
    protected function validateData($data)
    {
        if ($this->necessaryHeaders === null) {
            throw new \Exception('Necessary headers have not been defined');
        }
        $necessaryHeaderCount = count($this->necessaryHeaders);
        foreach ($data as $dataLine) {
            if (count($dataLine) < $necessaryHeaderCount) {
                throw new InvalidDataException();
            }
        }
    }

    /**
     * Actually imports the data. This method has to be overwritten by the importer.
     *
     * @param $additionalData
     * @param $headers
     * @param $data
     * @throws \Exception
     */
    protected function importData($additionalData, $headers, $data)
    {
        throw new \Exception('Data import has not been defined');
    }

    /**
     * Fetches a data point from $dataLine.
     *
     * @param $dataLine array A single data line
     * @param $headers array All headers
     * @param $header string The header for which to get the data point from $dataLine
     * @return string
     * @throws InvalidDataException
     */
    protected function getDataPoint($dataLine, $headers, $header)
    {
        foreach ($headers as $idx => $headerEntry) {
            if ($headerEntry === $header) {
                return utrim($dataLine[$idx]);
            }
        }
        throw new InvalidDataException('Could not find header '.$header.' in the data line '.json_encode($dataLine).' Headers: '.json_encode($headers));
    }

    /**
     * Checks if a specific header is provided.
     *
     * @param $headers array All headers
     * @param $header string The header we want to search for
     * @return string
     */
    protected function hasData($headers, $header)
    {
        return in_array($header, $headers);
    }

    /**
     * We only want to update the progress about every 2 seconds
     * Here we check if at least 2 seconds passed since the last update.
     *
     * @return bool
     */
    private function canUpdateProgress()
    {
        $now = time();
        if (! $this->lastProgressUpdate) {
            $this->lastProgressUpdate = $now;

            return true;
        }
        if ($this->lastProgressUpdate + 1 < $now) {
            $this->lastProgressUpdate = $now;

            return true;
        }

        return false;
    }

    /**
     * Sets the progress of the current step.
     *
     * @param float $progress 0-1
     */
    protected function setStepProgress($progress)
    {
        if ($this->import !== null && $this->canUpdateProgress()) {
            $progress = min(0.99, $progress);
            $this->import->setProgress(intval($this->import->getProgress()) + $progress);
        }
    }

    /**
     * Increase the progress to the next largest step.
     */
    protected function stepDone()
    {
        // Set the progress to the next largest int value
        // We don't check canUpdateProgress() here, because we want to always finalize the step
        if ($this->import !== null) {
            $this->import->setProgress(intval($this->import->getProgress()) + 1);
        }
    }

    /**
     * Marks the import as done.
     */
    protected function importDone()
    {
        if ($this->import !== null) {
            $this->import->setProgress($this->import->steps);
            $this->import->status = Import::STATUS_DONE;
            $this->import->save();
        }
    }
}
