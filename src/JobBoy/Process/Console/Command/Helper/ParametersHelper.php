<?php

namespace JobBoy\Process\Console\Command\Helper;

class ParametersHelper
{

    public static function resolveJsonParameters($parameters): array
    {

        $fileExists = file_exists($parameters);

        if ($fileExists) {
            $fileInfo = new \SplFileInfo($parameters);
            if (!$fileInfo->isFile()) {
                throw new \InvalidArgumentException('The given parameters file is not e regular file');
            }
            if (!$fileInfo->isReadable()) {
                throw new \InvalidArgumentException('The given parameters file is not readable');
            }
            $file = $fileInfo->openFile();
            $parameters = $file->fread($file->getSize());
        }

        $parameters = json_decode($parameters, true);

        if (is_null($parameters)) {

            $jsonError = json_last_error_msg();

            if ($fileExists) {
                $message = 'The given parameters option file does not contain a valid json string';
            } else {
                $message = 'The given parameters option is not a valid json string';
            }

            if ($jsonError) {

                $message .= ' ('.$jsonError.')';
            }

            throw new \InvalidArgumentException($message);
        }
        return $parameters;
    }

}