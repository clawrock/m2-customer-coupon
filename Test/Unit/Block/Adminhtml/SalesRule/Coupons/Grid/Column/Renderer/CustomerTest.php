<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Block\Adminhtml\SalesRule\Coupons\Grid\Column\Renderer;

use ClawRock\CustomerCoupon\Block\Account\Coupon;
use ClawRock\CustomerCoupon\Block\Adminhtml\SalesRule\Coupons\Grid\Column\Renderer\Customer;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    /**
     * @var \ClawRock\CustomerCoupon\Block\Account\Coupon
     */
    protected $block;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $urlInterface;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->urlInterface = $this->createMock(UrlInterface::class);

        $contextMock = $this->getMockBuilder(Context::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $contextMock->expects($this->once())
                    ->method('getUrlBuilder')
                    ->willReturn($this->urlInterface);

        $this->block = $objectManager->getObject(
            Customer::class,
            [
                'context' => $contextMock,
                'data'    => []
            ]
        );
    }

    public function testGetUpdateUrl()
    {
        $this->urlInterface->expects($this->once())
                               ->method('getUrl')
                               ->with('clawrock_customer/coupon/update')
                               ->willReturn('http://magento.com/clawrock_customer/coupon/update');
        $this->assertEquals(
            'http://magento.com/clawrock_customer/coupon/update',
            $this->block->getUpdateUrl()
        );
    }

    /**
     * @dataProvider rowProvider
     */
    public function testRender($rowData)
    {
        $val = $rowData['id'];

        $column = $this->getMockBuilder(\Magento\Framework\Data\Form\Element::class)
                       ->setMethods(['getId', 'getIndex', 'getInlineCss'])
                       ->disableOriginalConstructor()
                       ->getMock();

        $column->expects($this->once())
               ->method('getId')
               ->willReturn(1);

        $column->expects($this->once())
               ->method('getIndex')
               ->willReturn('id');

        $column->expects($this->once())
               ->method('getInlineCss')
               ->willReturn("test-class");

        $this->block->setColumn($column);

        $row = new \Magento\Framework\DataObject($rowData);
        $url = 'http://magento.com/clawrock_customer/coupon/update';

        $this->urlInterface->expects($this->exactly(2))
                               ->method('getUrl')
                               ->with('clawrock_customer/coupon/update')
                               ->willReturn($url);

        $expectedHtml =  '<input type="text" name="1" value="'.$val.'"class="input-text test-class"/>';
        $expectedHtml .= '<div class="clawrock-customer-coupon"><button class="clawrock-coupon-customer-update-button"';
        $expectedHtml .= 'onclick=\'updateCoupon('.$val.', "'.$url.'", 0); return false\'>';
        $expectedHtml .= new Phrase('Update').'</button>';
        $expectedHtml .= '<button class="clawrock-coupon-customer-remove-button"';
        $expectedHtml .= 'onclick=\'updateCoupon('.$val.', "'.$url.'", 1); return false\'>';
        $expectedHtml .= new Phrase('Remove').'</button>';
        $expectedHtml .= '<p class="clawrock-coupon-customer-message"></p></div>';

        $this->assertEquals($expectedHtml, $this->block->render($row));
    }

    public function rowProvider()
    {
        return [
            [['id'=> 1]],
            [['id'=> 3]],
            [['id'=> 2]]
        ];
    }
}
