<?php
namespace restphp\utils;

/**
 * Class RestSplitterUtils
 * @package restphp\utils
 */
class RestSplitterUtils {
    /**
     * @var string item 分隔符.
     */
    private $_firstSplitter;
    /**
     * @var string key value 分隔符.
     */
    private $_secondSplitter;

    /**
     * 初始
     * @param string $splitterChar item 分隔符.
     * @return RestSplitterUtils
     */
    public static function on($splitterChar='') {
        $splitterUtils = new RestSplitterUtils();
        if (!RestStringUtils::isBlank($splitterChar)) {
            $splitterUtils->setItemSeparator($splitterChar);
        }
        return $splitterUtils;
    }

    /**
     * @param string $separator item 分隔符.
     * @return RestSplitterUtils
     */
    public function setItemSeparator($separator) {
        $this->_firstSplitter = $separator;
        return $this;
    }

    /**
     * 设置key value 分隔符.
     * @param string $separator key value 分隔符.
     * @return RestSplitterUtils
     */
    public function withKeyValueSeparator($separator) {
        $this->_secondSplitter = $separator;
        return $this;
    }

    /**
     *
     * @param string $source 源.
     * @return array
     */
    public function split($source) {
        if (RestStringUtils::isBlank($source)) {
            return array();
        }

        $result = array();
        $arrItem = explode($this->_firstSplitter, $source);
        foreach ($arrItem as $item) {
            $arrKeyValue = explode($this->_secondSplitter, $item);
            $result[$arrKeyValue[0]] = isset($arrKeyValue[1]) ? $arrKeyValue[1] : null;
        }
        return $result;
    }
}