<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Controller\Account;

use ClawRock\CustomerCoupon\Controller\Account\Coupons;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\TestCase;

class CouponsTest extends TestCase
{
    /**
     * @var \ClawRock\CustomerCoupon\Controller\Account\Coupons
     */
    protected $controller;

    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $resultPage;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\View\Page\Title
     */
    protected $title;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $config;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->resultPage = $this->getMockBuilder(Page::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $this->resultPageFactory = $this->getMockBuilder(PageFactory::class)
                                        ->setMethods(['create'])
                                        ->disableOriginalConstructor()
                                        ->getMock();

        $this->resultPageFactory->expects($this->once())->method('create')->willReturn($this->resultPage);

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
                                      ->getMockForAbstractClass();

        $this->title = $objectManager->getObject(Title::class, [
            'scopeConfig' => $this->scopeConfig
        ]);

        $this->config = $this->createPartialMock(Config::class, ['getTitle']);

        $this->config->expects($this->any())->method('getTitle')->willReturn($this->title);

        $contextMock = $this->getMockBuilder(Context::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $this->controller = $objectManager->getObject(
            Coupons::class,
            [
                'context' => $contextMock,
                'resultPageFactory' => $this->resultPageFactory
            ]
        );
    }

    public function testExecuteResultPage()
    {
        $this->resultPage->expects($this->any())->method('getConfig')->willReturn($this->config);

        $this->assertSame($this->resultPage, $this->controller->execute());
        $this->assertEquals('My Coupons', $this->title->get());
    }
}
