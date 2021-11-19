<?php

namespace Google\Web_Stories_Dependencies\Sabberworm\CSS\Property;

use Google\Web_Stories_Dependencies\Sabberworm\CSS\Parsing\UnexpectedTokenException;
class KeyframeSelector extends Selector
{
    //Regexes for specificity calculations
    const SELECTOR_VALIDATION_RX = '/
	^(
		(?:
			[a-zA-Z0-9\\x{00A0}-\\x{FFFF}_^$|*="\'~\\[\\]()\\-\\s\\.:#+>]* # any sequence of valid unescaped characters
			(?:\\\\.)?                                              # a single escaped character
			(?:([\'"]).*?(?<!\\\\)\\2)?                              # a quoted text like [id="example"]
		)*
	)|
	(\\d+%)                                                          # keyframe animation progress percentage (e.g. 50%)
	$
	/ux';
}
