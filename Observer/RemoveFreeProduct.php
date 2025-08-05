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

class RemoveFreeProduct implements ObserverInterface
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
            $quote = $this->cart->getQuote();
            $freeSku=null;
            if ($this->giftHelper->isEnabled() && !empty($this->giftHelper->getItem())) {
                $freeSku=$this->giftHelper->getItem();
            }

            if($freeSku==null) {
                return;
            }
            $optionCode=$this->giftHelper->getCode();

            // Set price to 0
            $items = $quote->getAllVisibleItems();
            if (count($items) === 1) {
                $item = reset($items); // Get the first (and only) item

                if($item->getSku() == $freeSku) {
                    $option = $item->getOptionByCode($optionCode);
                    if ($option) {
                        $quote->removeItem($item->getItemId());
                        $quote->collectTotals()->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage() );
        }
    }
}
