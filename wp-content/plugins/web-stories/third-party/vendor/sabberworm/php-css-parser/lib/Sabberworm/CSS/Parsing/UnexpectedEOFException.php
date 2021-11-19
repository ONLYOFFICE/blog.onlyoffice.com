<?php

namespace Google\Web_Stories_Dependencies\Sabberworm\CSS\Parsing;

/**
* Thrown if the CSS parsers encounters end of file it did not expect
* Extends UnexpectedTokenException in order to preserve backwards compatibility
*/
class UnexpectedEOFException extends UnexpectedTokenException
{
}
