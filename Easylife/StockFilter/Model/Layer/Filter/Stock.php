<?php
/**
 * Easylife_StockFilter extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE_EASYLIFE_STOCK_FILTER.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category   	Easylife
 * @package	    Easylife_StockFilter
 * @copyright   Copyright (c) 2014 Marius Strajeru
 * @license	    http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Easylife\StockFilter\Model\Layer\Filter;

class Stock extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
{
    const IN_STOCK_COLLECTION_FLAG = 'easylife_stock_filter_applied';
    const CONFIG_FILTER_LABEL_PATH = 'easylife_stockfilter/settings/label';
    const CONFIG_URL_PARAM_PATH    = 'easylife_stockfilter/settings/url_param';
    protected $_activeFilter = false;
    protected $_requestVar = 'in-stock';
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);
        $this->_requestVar = $this->_scopeConfig->getValue(
            self::CONFIG_URL_PARAM_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $filter = $request->getParam($this->getRequestVar(), null);
        if (is_null($filter)) {
            return $this;
        }
        $this->_activeFilter = true;
        $filter = (int)(bool)$filter;
        $collection = $this->getLayer()->getProductCollection();
        $collection->setFlag(self::IN_STOCK_COLLECTION_FLAG, true);
        $collection->getSelect()->where('stock_status_index.stock_status = ?', $filter);
        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->getLabel($filter), $filter)
        );
        return $this;
    }
    /**
     * Get filter name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_scopeConfig->getValue(
            self::CONFIG_FILTER_LABEL_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get data array for building status filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        if ($this->getLayer()->getProductCollection()->getFlag(self::IN_STOCK_COLLECTION_FLAG)) {
            return [];
        }
        $data = [];
        foreach ($this->getStatuses() as $status) {
            $data[] = [
                'label' => $this->getLabel($status),
                'value' => $status,
                'count' => $this->getProductsCount($status)
            ];
        }
        return $data;
    }
    /**
     * get available statuses
     * @return array
     */
    public function getStatuses()
    {
        return [
            \Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK,
            \Magento\CatalogInventory\Model\Stock::STOCK_OUT_OF_STOCK
        ];
    }
    /**
     * @return array
     */
    public function getLabels()
    {
        return [
            \Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK => __('In Stock'),
            \Magento\CatalogInventory\Model\Stock::STOCK_OUT_OF_STOCK => __('Out of stock'),
        ];
    }
    /**
     * @param $value
     * @return string
     */
    public function getLabel($value)
    {
        $labels = $this->getLabels();
        if (isset($labels[$value])) {
            return $labels[$value];
        }
        return '';
    }

    /**
     * @param $value
     * @return string
     */
    public function getProductsCount($value)
    {
        $collection = $this->getLayer()->getProductCollection();
        $select = clone $collection->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(\Zend_Db_Select::COLUMNS);
        $select->reset(\Zend_Db_Select::ORDER);
        $select->reset(\Zend_Db_Select::LIMIT_COUNT);
        $select->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $select->where('stock_status_index.stock_status = ?', $value);
        $select->columns(
            [
                'count' => new \Zend_Db_Expr("COUNT(e.entity_id)")
            ]
        );
        return $collection->getConnection()->fetchOne($select);
    }
}
