<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Block;

use Magento\Mtf\Block\Block;

/**
 * Customer order view messages block.
 */
class Messages extends Block
{
    /**
     * Css selector for success message.
     *
     * @var string
     */
    private $successMessage = '[data-ui-id="message-success"]';

    /**
     * Wait for requisition list created success message.
     *
     * @return void
     */
    public function waitForSuccessMessage()
    {
        $this->waitForElementVisible($this->successMessage);
    }
}
