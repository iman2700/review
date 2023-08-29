<?php

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class CarsApiResource
{
    public $method = "GET";
    public $path = "/";
    public $output;
    public $summary;
    public $description;
}