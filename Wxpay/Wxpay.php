<?php
/**
 * @desc 微信APP支付类
 * @author shangheguang@yeah.net
 * @date 2015-08-24
 */

class Wxpay {
	
	//参数配置
	private $config;

    private $WxPayHelper;
	
	function __construct($config)
	{
		require_once 'WxPayHelper.php';
		$this->WxPayHelper = new WxPayHelper();
		$this->setConfig($config);
	}

	public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * JSAPI统一下单
     */
	public function createOrder($params)
	{
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		
        $data['appid'] 		      = $this->config['appid'];//微信开放平台审核通过的应用APPID
        $data['body'] 		      = $params['body'];//商品或支付单简要描述
        $data['mch_id'] 	      = $this->config['mch_id'];//商户号
        $data['nonce_str'] 	      = $this->WxPayHelper->getRandChar(32);//随机字符串
        $data['notify_url']       = $params['notify_url'];//支付回调地址
        $data['out_trade_no']     = $params['out_trade_no'];//商户订单号
        $data['spbill_create_ip'] = $this->WxPayHelper->get_client_ip();//终端IP
        $data['total_fee']        = $params['total_fee'];//总金额
        $data['time_expire']	  = $params['time_expire'];//交易结束时间
        $data['trade_type']   	  = $params['trade_type'];//交易类型
        $data['openid']   	      = $params['openid'];//交易类型
        $data['sign'] 			  = $this->WxPayHelper->getSign($data, $this->config['api_key']);//签名

        $xml = $this->WxPayHelper->arrayToXml($data);
        $response = $this->WxPayHelper->postXmlCurl($xml, $url);

        //将微信返回的结果xml转成数组
        $responseArr = $this->WxPayHelper->xmlToArray($response);
        if(isset($responseArr["return_code"]) && $responseArr["return_code"]=='SUCCESS'){
        	return 	$this->getOrder($responseArr['prepay_id']);
        }
        return $responseArr;
	}
	
	/**
	 * 执行第二次签名，才能返回给客户端使用
	 * @param int $prepayId:预支付交易会话标识
	 * @return array
	 */
	public function getOrder($prepayId)
	{
		$data['appid'] 		= $this->config['appid'];
		$data['noncestr'] 	= $this->WxPayHelper->getRandChar(32);
		$data['package'] 	= 'Sign=WXPay';
		$data['partnerid'] 	= $this->config['mch_id'];
		$data['prepayid'] 	= $prepayId;
		$data['timestamp'] 	= time();
		$data['sign'] 		= $this->WxPayHelper->getSign($data, $this->config['api_key']);
		$data['packagestr'] = 'Sign=WXPay';
		return $data;
	}
	
	/**
	 * 异步通知信息验证
	 * @return boolean|mixed
	 */
	public function verifyNotify()
	{
		$xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
		if(!$xml){
			return false;
		}
		$wx_back = $this->WxPayHelper->xmlToArray($xml);
		if(empty($wx_back)){
			return false;
		}
		$checkSign = $this->WxPayHelper->getVerifySign($wx_back, $this->config['api_key']);		
		if($checkSign==$wx_back['sign']){
			return $wx_back;
		}	return false;
	}

    /**
     * 微信退款
     */
    public function refund($params,$cert)
    {
        $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

        $data['appid'] 		      = $this->config['appid'];//微信开放平台审核通过的应用APPID
        $data['mch_id'] 	      = $this->config['mch_id'];//商户号
        $data['nonce_str'] 	      = $this->WxPayHelper->getRandChar(32);//随机字符串
        $data['out_trade_no']     = $params['out_trade_no'];//商户订单号
        $data['out_refund_no']    = $params['out_refund_no'];//商户退款单号
        $data['spbill_create_ip'] = $this->WxPayHelper->get_client_ip();//终端IP
        $data['total_fee']        = $params['total_fee'];//总金额
        $data['refund_fee']       = $params['refund_fee'];//退款金额
        $data['notify_url']       = $params['notify_url'];//退款回调地址
        $data['sign'] 			  = $this->WxPayHelper->getSign($data, $this->config['api_key']);//签名

        $xml = $this->WxPayHelper->arrayToXml($data);
        $response 	= $this->WxPayHelper->postXmlCurl($xml, $url,60,true, $cert['sslcert_path'],$cert['sslkey_path']);
        //将微信返回的结果xml转成数组
        $responseArr = $this->WxPayHelper->xmlToArray($response);
        return $responseArr;
    }

    /**
     * 微信支付回调
     */
    public function notify()
    {
        $xml = file_get_contents('php://input');
        $json = json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA));
        $result = json_decode($json, true);
        if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
            //校验微信签名，防止恶意回调
            $wx_sign = $result['sign'];
            unset($result['sign']);
            $sign = $this->WxPayHelper->getSign($result, $this->config['api_key']);
            if($wx_sign != $sign){
                //签名不同直接结束
                exit("failure");
            }
            //回调成功，停止微信回调
            return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }
    }

	function __destruct() {
		
	}
	
}

