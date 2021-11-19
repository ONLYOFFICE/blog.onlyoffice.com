<?php

namespace Google\Web_Stories_Dependencies\Sabberworm\CSS\Comment;

use Google\Web_Stories_Dependencies\Sabberworm\CSS\Renderable;
class Comment implements Renderable
{
    protected $iLineNo;
    protected $sComment;
    public function __construct($sComment = '', $iLineNo = 0)
    {
        $this->sComment = $sComment;
        $this->iLineNo = $iLineNo;
    }
    /**
     * @return string
     */
    public function getComment()
    {
        return $this->sComment;
    }
    /**
     * @return int
     */
    public function getLineNo()
    {
        return $this->iLineNo;
    }
    /**
     * @return string
     */
    public function setComment($sComment)
    {
        $this->sComment = $sComment;
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render(new \Google\Web_Stories_Dependencies\Sabberworm\CSS\OutputFormat());
    }
    /**
     * @return string
     */
    public function render(\Google\Web_Stories_Dependencies\Sabberworm\CSS\OutputFormat $oOutputFormat)
    {
        return '/*' . $this->sComment . '*/';
    }
}
