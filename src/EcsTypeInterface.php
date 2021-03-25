<?php
namespace PimJansen\Monolog\Formatter;

use JsonSerializable;

interface EcsTypeInterface extends JsonSerializable
{
    public function getTypeName(): string;
}