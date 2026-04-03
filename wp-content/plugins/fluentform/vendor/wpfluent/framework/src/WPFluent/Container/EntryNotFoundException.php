<?php

namespace FluentForm\Framework\Container;

use Exception;
use FluentForm\Framework\Container\Contracts\Psr\NotFoundExceptionInterface;

class EntryNotFoundException extends Exception implements NotFoundExceptionInterface
{
    //
}
