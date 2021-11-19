<?php

namespace Google\Web_Stories_Dependencies\Sabberworm\CSS;

interface Renderable
{
    public function __toString();
    public function render(\Google\Web_Stories_Dependencies\Sabberworm\CSS\OutputFormat $oOutputFormat);
    public function getLineNo();
}
