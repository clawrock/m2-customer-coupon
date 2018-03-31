<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Controller\Adminhtml\Coupon;

use ClawRock\CustomerCoupon\Controller\Account\Coupons;
use ClawRock\CustomerCoupon\Controller\Adminhtml\Coupon\Update;
use ClawRock\CustomerCoupon\Helper\Coupon as CouponHelper;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\Response\Http as Response;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Translate\InlineInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use PHPUnit\Framework\TestCase;

class UpdateTest extends TestCase
{
    /**
     * @var \ClawRock\CustomerCoupon\Controller\Adminhtml\Coupon\Update
     */
    protected $controller;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $rule;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $coupon;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var \ClawRock\CustomerCoupon\Helper\Coupon
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $translateInline;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    protected $resultJson;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->helper = $this->getMockBuilder(CouponHelper::class)
                             ->setMethods(['loadCoupon', 'loadRule', 'prepareCustomerIdByEmail'])
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->translateInline = $this->getMockBuilder(InlineInterface::class)
                                      ->setMethods([
                                        'processResponseBody',
                                        'getParser',
                                        'isAllowed',
                                        'getAdditionalHtmlAttribute'
                                      ])
                                      ->disableOriginalConstructor()
                                      ->getMock();

        $this->translateInline->expects($this->once())
                                      ->method('processResponseBody')
                                      ->willReturnSelf();

        $this->resultJson = $objectManager->getObject(
            Json::class,
            [
                'translateInline' => $this->translateInline
            ]
        );

        $this->resultJsonFactory = $this->getMockBuilder(JsonFactory::class)
                                        ->setMethods(['create'])
                                        ->disableOriginalConstructor()
                                        ->getMock();

        $this->resultJsonFactory->expects($this->once())->method('create')->willReturn($this->resultJson);

        $this->request = $this->createPartialMock(
            Request::class,
            ['isAjax', 'getPost']
        );

        $contextMock = $this->getMockBuilder(Context::class)
                            ->setMethods(['getRequest'])
                            ->disableOriginalConstructor()
                            ->getMock();

        $contextMock->expects($this->once())->method('getRequest')->willReturn($this->request);

        $this->controller = $objectManager->getObject(
            Update::class,
            [
                'context' => $contextMock,
                'resultJsonFactory' => $this->resultJsonFactory,
                'couponHelper' => $this->helper
            ]
        );
    }

    protected function prepareRequest($couponId, $customerEmail, $remove)
    {
        $postData = [
            'couponId' => $couponId,
            'customerEmail' => $customerEmail,
            'remove' => $remove
        ];

        $this->request->expects($this->once())->method('isAjax')->willReturn(true);
        $this->request->expects($this->once())->method('getPost')->willReturn($postData);

        $this->coupon = $this->getMockBuilder(Coupon::class)
                             ->setMethods(['getRuleId', 'save'])
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->rule = $this->getMockBuilder(Rule::class)
                           ->setMethods(['getWebsiteIds'])
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->response = $this->getMockBuilder(Response::class)
                                   ->setMethods()
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->helper->expects($this->once())->method('loadCoupon')->willReturn($this->coupon);
    }

    /**
     * @dataProvider postProvider
     */
    public function testExecuteResultJson($couponId, $customerEmail, $remove, $message)
    {

        $customerId = 1;
        $ruleId = 1;

        $this->prepareRequest($couponId, $customerEmail, $remove);

        $this->helper->expects($this->once())->method('loadRule')->willReturn($this->rule);
        $this->helper->expects($this->once())->method('prepareCustomerIdByEmail')->willReturn($customerId);

        $this->coupon->expects($this->once())->method('getRuleId')->willReturn($ruleId);
        $this->coupon->expects($this->once())->method('save')->willReturnSelf();

        $this->rule->expects($this->once())->method('getWebsiteIds')->willReturn([]);

        $result = $this->controller->execute();

        $result->renderResult($this->response);

        $response = json_decode($this->response->getContent(), true);

        $this->assertEquals($message, $response['message']);
        $this->assertSame($this->resultJson, $result);
        $this->assertEquals($customerId, $this->coupon->getCouponCustomerId());
    }

    /**
     * @dataProvider postProvider
     */
    public function testException($couponId, $customerEmail, $remove, $message)
    {
        $exceptionMessage = 'Exception message.';

        $this->prepareRequest($couponId, $customerEmail, $remove, $message);

        $this->coupon->expects($this->once())
                     ->method('getRuleId')
                     ->willThrowException(new \Exception($exceptionMessage));

        $result = $this->controller->execute();

        $result->renderResult($this->response);

        $response = json_decode($this->response->getContent(), true);

        $this->assertEquals($exceptionMessage, $response['message']);
        $this->assertEquals(1, $response['error']);
    }

    public function postProvider()
    {
        return [
            [1, 'test@test.com', true, 'Customer has been removed from coupon.'],
            [1, 'test@test.com', false, 'Customer has been assigned to the coupon.'],
        ];
    }
}
