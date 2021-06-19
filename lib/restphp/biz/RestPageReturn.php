<?php
namespace restphp\biz;
/**
 * Created by zj.
 * User: zj
 * Date: 2019/11/25 0025
 * Time: 下午 9:19
 */


class RestPageReturn {
    /**
     * @var integer.
     */
    private $_total = 0;

    /**
     * @var array
     */
    private $_items = array();

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->_total;
    }

    /**
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->_total = $total;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * @param array $items
     */
    public function setItems($items)
    {
        $this->_items = $items;
    }

    /**
     * to array.
     * @return array
     */
    public function toArray() {
        return array(
            'total' => $this->getTotal(),
            'items' => $this->getItems()
        );
    }

    /**
     * to json string.
     * @return string
     */
    public function toJson() {
        return json_encode($this->toArray());
    }
}