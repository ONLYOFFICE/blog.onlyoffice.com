<?php

namespace Google\Web_Stories_Dependencies\Sabberworm\CSS\RuleSet;

use Google\Web_Stories_Dependencies\Sabberworm\CSS\Property\AtRule;
/**
 * A RuleSet constructed by an unknown @-rule. @font-face rules are rendered into AtRuleSet objects.
 */
class AtRuleSet extends RuleSet implements AtRule
{
    private $sType;
    private $sArgs;
    public function __construct($sType, $sArgs = '', $iLineNo = 0)
    {
        parent::__construct($iLineNo);
        $this->sType = $sType;
        $this->sArgs = $sArgs;
    }
    public function atRuleName()
    {
        return $this->sType;
    }
    public function atRuleArgs()
    {
        return $this->sArgs;
    }
    public function __toString()
    {
        return $this->render(new \Google\Web_Stories_Dependencies\Sabberworm\CSS\OutputFormat());
    }
    public function render(\Google\Web_Stories_Dependencies\Sabberworm\CSS\OutputFormat $oOutputFormat)
    {
        $sArgs = $this->sArgs;
        if ($sArgs) {
            $sArgs = ' ' . $sArgs;
        }
        $sResult = "@{$this->sType}{$sArgs}{$oOutputFormat->spaceBeforeOpeningBrace()}{";
        $sResult .= parent::render($oOutputFormat);
        $sResult .= '}';
        return $sResult;
    }
}
