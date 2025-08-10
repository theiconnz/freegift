<?php

namespace Theiconnz\Freegift\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Theiconnz\Freegift\Helper\Freegift;
use Psr\Log\LoggerInterface;

class AddFreeProduct implements ObserverInterface
{
    protected $productRepository;
    protected $cart;
    protected $messageManager;
    protected $giftHelper;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Json
     */
    private $serializer;

    public function __construct(
        ProductRepository $productRepository,
        Cart $cart,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        Freegift $giftHelper,
        Json $serializer
    ) {
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->messageManager = $messageManager;
        $this->giftHelper = $giftHelper;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    public function execute(Observer $observer)
    {
        try {
            $cart = $this->cart->getQuote();

            $freeSku=null;
            if ($this->giftHelper->isEnabled() && !empty($this->giftHelper->getItem())) {
                $freeSku=$this->giftHelper->getItem();
            }

            if($freeSku==null) {
                return;
            }

            $optionCode=$this->giftHelper->getCode();

            $quantity = $this->giftHelper->getNoofItem();

            // Check if free item is already in cart
            foreach ($cart->getAllVisibleItems() as $item) {
                if ($item->getSku() == $freeSku) {
                    // already have item in cart, so check the item option for free item code
                    $option = $item->getOptionByCode($optionCode);
                    if ($option) {
                        return;
                    }
                }
            }

            // Load product by SKU
            $product = $this->productRepository->get($freeSku);



            $message = $this->giftHelper->getMessage();

            $buyRequest = new \Magento\Framework\DataObject([
                'qty' => $quantity,
                'custom_price' => 0,
            ]);

            // Set price to 0
            if(!empty($message)) {
                $product->prepareCustomOptions();
                $additionOptions = [[
                    'label' => $optionCode,
                    'value' => $message,
                ]];
                $product->addCustomOption($optionCode, $message); // marker for later use
                $product->addCustomOption('additional_options',
                    $this->serializer->serialize($additionOptions)
                ); // marker for later use
            }

            $cart->addProduct($product, $buyRequest);
            $cart->save();

            // Set custom price to 0 after adding (just in case)
            foreach ($cart->getAllItems() as $item) {
                if ($item->getSku() === $freeSku) {
                    $option = $item->getOptionByCode($optionCode);
                    if ($option) {
                        $item->setCustomPrice(0);
                        $item->setOriginalCustomPrice(0);
                        $item->getProduct()->setIsSuperMode(true);
                        return;
                    }
                }
            }
            $cart->save();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage() );
        }
    }
}
