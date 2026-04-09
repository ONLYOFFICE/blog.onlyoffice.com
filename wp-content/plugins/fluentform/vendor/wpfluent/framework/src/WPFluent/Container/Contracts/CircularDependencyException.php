<?php

namespace FluentForm\Framework\Container\Contracts;

use Exception;
use FluentForm\Framework\Container\Contracts\Psr\ContainerExceptionInterface;

class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
