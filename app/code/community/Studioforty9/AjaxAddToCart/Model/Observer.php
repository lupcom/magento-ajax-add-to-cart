<?php
/**
 * Studioforty9_AjaxAddToCart
 *
 * @category  Studioforty9
 * @package   Studioforty9_AjaxAddToCart
 * @author    StudioForty9 <info@studioforty9.com>
 * @copyright 2016 StudioForty9 (http://www.studioforty9.com)
 * @license   https://github.com/studioforty9/add-to-cart/blob/master/LICENCE BSD
 * @version   0.0.1
 * @link      https://github.com/studioforty9/add-to-cart
 */

/**
 * Studioforty9_AjaxAddToCart_Model_Observer
 *
 * @category   Studioforty9
 * @package    Studioforty9_AjaxAddToCart
 * @subpackage Block
 */
class Studioforty9_AjaxAddToCart_Model_Observer
{
    /**
     * When the request is XMLHttpRequest on `checkout_cart_add_product_complete`.
     * Return JSON instead and stop the default redirect.
     *
     * @event checkout_cart_add_product_complete
     * @param Varien_Event_Observer $observer
     */
    public function onAddToCart(Varien_Event_Observer $observer)
    {
        $request = Mage::app()->getRequest();
        if ($request->isAjax()) {
            $product = $observer->getProduct();
            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
            $observer->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(array(
                    'productName' => $product->getName(),
                    'productQty' => $product->getQty(),
                    'productUrl' => $product->getProductUrl(),
                    'productImageUrl' => (string) Mage::helper('catalog/image')->init($product, 'thumbnail', null, 'minicart_thumb'),
                    'productFormattedPrice' => Mage::helper('core')->currency($product->getFinalPrice(), true, false),
                    'success' => 'true',
                    'redirectTo' => false,
                    'cartCount' => Mage::getSingleton('checkout/cart')->getSummaryQty(),
                    'html' => array(
                        'result' => $this->getSuccessHTML()->toHtml(),
                        'minicart' => $this->getMiniCartBlock()->toHtml()
                    )
                ))
            );
        }
    }

    /**
     * The inline success template.
     *
     * @return Mage_Core_Block_Template
     */
    private function getSuccessHtml()
    {
        return Mage::app()->getLayout()->createBlock(
            'core/template',
            'studioforty9.ajaxaddtocart.success',
            array(
                'template' => 'studioforty9/ajaxaddtocart/success.phtml'
            )
        );
    }

    /**
     * The minicart block.
     *
     * @return Mage_Checkout_Block_Cart_Sidebar
     */
    private function getMiniCartBlock()
    {
        $layout = Mage::app()->getLayout();

        /** @var Mage_Checkout_Block_Cart_Sidebar $minicartHead */
        $minicartContent = $layout->createBlock('checkout/cart_sidebar', 'minicart_content');
        $minicartContent->setTemplate('checkout/cart/minicart/items.phtml');

        $renderTemplate = 'checkout/cart/minicart/default.phtml';
        $minicartContent->addItemRender('default', 'checkout/cart_item_renderer', $renderTemplate);
        $minicartContent->addItemRender('simple', 'checkout/cart_item_renderer', $renderTemplate);
        $minicartContent->addItemRender('grouped', 'checkout/cart_item_renderer_grouped', $renderTemplate);
        $minicartContent->addItemRender('configurable', 'checkout/cart_item_renderer_configurable', $renderTemplate);

        return $minicartContent;
    }
}
