<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that order is correct in Admin.
 */
class AssertOrderCorrectInAdmin extends AbstractConstraint
{
    /**
     * Assert that order is correct in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param \Magento\SalesRule\Test\Fixture\SalesRule $salesRule
     * @param string $orderId
     * @param array $products
     * @param array $qtys
     * @param int $tax
     * @param string $discountType
     * @param int $discountValue
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $salesRule,
        $orderId,
        array $products,
        array $qtys,
        $tax,
        $discountType,
        $discountValue
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        $this->checkTotals($salesOrderView, $products, $qtys, $tax, $discountType, $discountValue, $salesRule);
    }

    /**
     * Assert that order is correct in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param array $products
     * @param array $qtys
     * @param int $tax
     * @param string $discountType
     * @param int $discountValue
     * @param \Magento\SalesRule\Test\Fixture\SalesRule|null $salesRule
     * @return void
     */
    public function checkTotals(
        SalesOrderView $salesOrderView,
        array $products,
        array $qtys,
        $tax,
        $discountType,
        $discountValue,
        $salesRule
    ) {
        $totals = $salesOrderView->getNegotiableSectionTotalsBlock()->getTotals();
        $subtotal = $this->getSubtotal($products, $qtys, $salesRule);
        $subtotalInclTax = $subtotal + ($subtotal * $tax) / 100;

        switch ($discountType) {
            case 'amount':
                $discountInclTax = $discountExclTax = $discountValue;
                break;
            case 'percentage':
                $discountInclTax = $subtotalInclTax * $discountValue / 100;
                $discountExclTax = $subtotal * $discountValue / 100;
                break;
            case 'proposed':
                $discountInclTax = $subtotalInclTax - $discountValue;
                $discountExclTax = $subtotal - $discountValue;
                break;
            default:
                $discountInclTax = $discountExclTax = 0;
        }

        $this->isQuoteTotalsCorrect(
            $totals,
            $subtotal,
            $subtotalInclTax,
            $discountInclTax,
            $discountExclTax
        );
    }

    /**
     * Get quote subtotals.
     *
     * @param array $products
     * @param array $qtys
     * @param \Magento\SalesRule\Test\Fixture\SalesRule|null $salesRule
     * @return float|int
     */
    private function getSubtotal(array $products, array $qtys, $salesRule)
    {
        $subtotal = 0;
        $i = 0;

        foreach ($products as $product) {
            $price = $product->getData('price') * $qtys[$i];
            if ($salesRule) {
                $price = $price * (100 - $salesRule->getData()['discount_amount']) / 100;
            }
            $subtotal += $price;
            $i++;
        }

        return $subtotal;
    }

    /**
     * Check is quote totals correct.
     *
     * @param array $totals
     * @param float $subtotal
     * @param float $subtotalInclTax
     * @param float $discountInclTax
     * @param float $discountExclTax
     * @return void
     */
    private function isQuoteTotalsCorrect(
        array $totals,
        $subtotal,
        $subtotalInclTax,
        $discountInclTax,
        $discountExclTax
    ) {
        $isQuoteTotalsCorrect = true;

        $quoteTotalsData = [
            'col-catalog_price_excl_tax' => $subtotal,
            'col-catalog_price_incl_tax' => $subtotalInclTax,
            'col-negotiated_discount' => $discountInclTax,
            'col-subtotal_excl' => ($subtotal - $discountExclTax),
            'col-subtotal_incl' => ($subtotalInclTax - $discountInclTax)
        ];

        foreach ($quoteTotalsData as $totalsKey => $totalsValue) {
            $isQuoteTotalsCorrect = $this->isQuoteTotalsValueCorrect($totals, $totalsKey, $totalsValue);

            if (!$isQuoteTotalsCorrect) {
                break;
            }
        }

        \PHPUnit_Framework_Assert::assertTrue(
            $isQuoteTotalsCorrect,
            'Order totals are not correct.'
        );
    }

    /**
     * Is quote totals item has correct value.
     *
     * @param array $totals
     * @param string $totalsKey
     * @param float $totalsValue
     * @return bool
     */
    private function isQuoteTotalsValueCorrect(array $totals, $totalsKey, $totalsValue)
    {
        $isQuoteTotalsCorrect = true;

        if (isset($totals[$totalsKey]) && strpos($totals[$totalsKey], number_format($totalsValue, 2)) === false) {
            $isQuoteTotalsCorrect = false;
        }

        return $isQuoteTotalsCorrect;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order totals are correct.';
    }
}
