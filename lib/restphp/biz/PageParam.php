<?php
namespace restphp\biz;

class PageParam {
    /**
     * @var object
     */
    private $_offsetId;

    /**
     * @var integer.
     */
    private $_page;

    /**
     * @var integer.
     */
    private $_size;

    /**
     * MySQL select from.
     * @return float|int
     */
    public function getFrom () {
        $nPage = $this->getPage() > 0 ? $this->getPage() : 1;
        return ($nPage - 1) * $this->getSize();
    }

    /**
     * MySQL select limit.
     * @return int
     */
    public function getLimit() {
        return $this->getSize();
    }


    /**
     * @return object
     */
    public function getOffsetId()
    {
        return $this->_offsetId;
    }

    /**
     * @param object $offsetId
     */
    public function setOffsetId($offsetId)
    {
        $this->_offsetId = $offsetId;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->_page;
    }

    /**
     * @param int $page
     */
    public function setPage($page)
    {
        $this->_page = $page;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->_size = $size;
    }
}