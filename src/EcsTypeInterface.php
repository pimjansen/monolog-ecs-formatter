<?php
namespace ECS\Formatter;

use JsonSerializable;

interface EcsTypeInterface extends JsonSerializable
{
    public function getTypeName(): string;
}