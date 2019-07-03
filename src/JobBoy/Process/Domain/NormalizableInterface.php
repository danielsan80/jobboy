<?php


namespace JobBoy\Process\Domain;


interface NormalizableInterface
{


    public function normalize(): array;

    public static function denormalize(array $data);
}