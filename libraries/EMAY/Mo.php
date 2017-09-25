<?php
namespace MLIB\EMAY;
class Mo{

    /**
     * 发送者附加码
     */
    var $addSerial;

    /**
     * 接收者附加码
     */
    var $addSerialRev;

    /**
     * 通道号
     */
    var $channelnumber;

    /**
     * 手机号
     */
    var $mobileNumber;

    /**
     * 发送时间
     */
    var $sentTime;

    /**
     * 短信内容
     */
    var $smsContent;
    function __construct(&$ret=array())
    {
        $this->Mo($ret);
    }

    function Mo(&$ret=array())
    {
        $this->addSerial = $ret[addSerial];
        $this->addSerialRev = $ret[addSerialRev];
        $this->channelnumber = $ret[channelnumber];
        $this->mobileNumber = $ret[mobileNumber];
        $this->sentTime = $ret[sentTime];
        $this->smsContent = $ret[smsContent];

    }

    function getAddSerial()
    {
        return $this->addSerial;
    }
    function getAddSerialRev()
    {
        return $this->addSerialRev;
    }
    function getChannelnumber()
    {
        return $this->channelnumber;
    }
    function getMobileNumber()
    {
        return $this->mobileNumber;
    }
    function getSentTime()
    {
        return $this->sentTime;
    }
    function getSmsContent()
    {
        return $this->smsContent;
    }




}