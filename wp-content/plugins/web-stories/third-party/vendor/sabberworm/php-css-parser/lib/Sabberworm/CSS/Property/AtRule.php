<?php

namespace Google\Web_Stories_Dependencies\Sabberworm\CSS\Property;

use Google\Web_Stories_Dependencies\Sabberworm\CSS\Renderable;
use Google\Web_Stories_Dependencies\Sabberworm\CSS\Comment\Commentable;
interface AtRule extends Renderable, Commentable
{
    // Since there are more set rules than block rules, we’re whitelisting the block rules and have anything else be treated as a set rule.
    const BLOCK_RULES = 'media/document/supports/region-style/font-feature-values';
    // …and more font-specific ones (to be used inside font-feature-values)
    const SET_RULES = 'font-face/counter-style/page/swash/styleset/annotation';
    public function atRuleName();
    public function atRuleArgs();
}
