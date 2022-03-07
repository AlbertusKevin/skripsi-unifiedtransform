<?php

namespace App\Interfaces;

interface MarkInterface {
    public function create($rows);
    public function getMarks(array $data, string $type, array $with = []);
    public function storeFinalMarks($rows);
}