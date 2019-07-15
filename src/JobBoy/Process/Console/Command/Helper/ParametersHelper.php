<?php

namespace JobBoy\Process\Console\Command\Helper;

class ParametersHelper
{

    public static function resolveJsonParameters($parameters): array
    {
        if (file_exists($parameters)) {
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
            $message = json_last_error_msg();
            if (!$message) {
                $message = 'The given parameters option is not a valid json string or file';
            }
            throw new \InvalidArgumentException($message);
        }
        return $parameters;
    }

}