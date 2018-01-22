<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Api\Data\TierPriceInterface;

/**
 * Class ConfigureSharedCatalogStep.
 * Configure shared catalog step.
 */
class ConfigureSharedCatalogStep implements TestStepInterface
{
    /**
     * @var SharedCatalogIndex $sharedCatalogIndex
     */
    protected $sharedCatalogIndex;

    /**
     * @var SharedCatalogConfigure $sharedCatalogConfigure
     */
    protected $sharedCatalogConfigure;

    /**
     * Shared catalog.
     *
     * @var SharedCatalog
     */
    protected $sharedCatalog;

    /**
     * Catalog Product.
     *
     * @var CatalogProductSimple
     */
    protected $products;

    /**
     * @var array
     */
    protected $data;

    /**
     * @constructor
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param SharedCatalog $sharedCatalog
     * @param array $products
     * @param array $data [optional]
     */
    public function __construct(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure,
        SharedCatalog $sharedCatalog,
        array $products,
        array $data = []
    ) {
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogConfigure = $sharedCatalogConfigure;
        $this->sharedCatalog = $sharedCatalog;
        $this->products = $products;
        $this->data = $data;
    }

    /**
     * Configure shared catalog.
     *
     * @return void
     */
    public function run()
    {
        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGrid()->search(['name' => $this->sharedCatalog->getName()]);
        $this->sharedCatalogIndex->getGrid()->openConfigure($this->sharedCatalogIndex->getGrid()->getFirstItemId());
        $this->sharedCatalogConfigure->getContainer()->openConfigureWizard();
        foreach ($this->products as $product) {
            $this->sharedCatalogConfigure->getStructureGrid()->checkSwitcherItem(['sku' => $product->getSku()]);
        }
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure
            ->getPricingGrid()
            ->resetFilter();
        if (!empty($this->data['discount'])) {
            if ($this->data['type'] == TierPriceInterface::PRICE_TYPE_DISCOUNT) {
                $this->sharedCatalogConfigure
                    ->getPricingGrid()
                    ->applyDiscount();
            } elseif ($this->data['type'] == TierPriceInterface::PRICE_TYPE_FIXED) {
                $this->sharedCatalogConfigure
                    ->getPricingGrid()
                    ->adjustFixedPrice();
            }
            $this->sharedCatalogConfigure->getDiscount()->setAlertText($this->data['discount']);
            $this->sharedCatalogConfigure->getDiscount()->acceptAlert();
            $this->sharedCatalogConfigure->getPricingGrid()->waitForLoader();
        }
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPageActionBlock()->save();
    }
}
