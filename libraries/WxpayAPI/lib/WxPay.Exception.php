<?php
/**
 *
 * 微信支付API异常类
 * @author widyhu
 *
 */
namespace MLIB\WXPAY;
class WxPayException extends \Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
