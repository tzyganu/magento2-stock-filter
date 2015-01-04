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
namespace Easylife\StockFilter\Model\Source;

class Position
{
    const POSITION_TOP = 'top';
    const POSITION_BOTTOM = 'bottom';
    const POSITION_AFTER_CATEGORY = 'after_category';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::POSITION_TOP,
                'label' => __('At the top')
            ],
            [
                'value' => self::POSITION_BOTTOM,
                'label' => __('At the bottom')
            ],
            [
                'value' => self::POSITION_AFTER_CATEGORY,
                'label' => __('After the category filter')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $values = [];
        foreach ($this->toOptionArray() as $item) {
            $values[$item['value']] = $item['label'];
        }
        return $values;
    }
}
