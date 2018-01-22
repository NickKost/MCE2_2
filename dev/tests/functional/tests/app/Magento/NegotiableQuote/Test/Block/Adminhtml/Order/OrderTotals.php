<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml\Order;

use Magento\Mtf\Block\Block;

/**
 * Order totals block on Order page.
 */
class OrderTotals extends Block
{
    /**
     * Order totals price row selector.
     *
     * @var string
     */
    protected $totals = '.data-table>tbody>tr';

    /**
     * Order totals price selector in price row.
     *
     * @var string
     */
    protected $totalPrice = '.price';

    /**
     * Returns array of quote totals.
     *
     * @return array
     */
    public function getTotals()
    {
        $totals = [];
        $rows = $this->_rootElement->getElements($this->totals);
        foreach ($rows as $row) {
            if (count($row->getElements($this->totalPrice))) {
                $totals[$row->getAttribute('class')] = $row->find($this->totalPrice)->getText();
            }
        }

        return $totals;
    }

    /**
     * Returns array of quote totals when display and base currencies differ.
     *
     * @return array
     */
    public function getTotalsWithDifferentCurrencies()
    {
        $totals = [];
        $rows = $this->_rootElement->getElements($this->totals);
        foreach ($rows as $row) {
            $prices = $row->getElements($this->totalPrice);
            if (count($prices)) {
                foreach ($prices as $total) {
                    $totals[$row->getAttribute('class')][] = $total->getText();
                }
            }
        }

        return $totals;
    }
}
