<?php

declare(strict_types=1);

namespace Zhubaiming\Cornerstone;

class Patchwerk
{
    /**
     * 原始输入参数(未做任何修改)
     */
    private array $parametersOrigin = [];

    /**
     * 输入参数(实际会在插件执行过程中进行修改，产生新的key)
     */
    private array $parameters = [];

    /**
     * 有效荷载(实际存储请求 API 时所需要的所有有效参数)
     */
    private $payload = null;

    /**
     * 打包器，可根据打包类型不同，使用不同的打包器(如: json类型打包器，query类型打包器，xml类型打包器等)
     */
    private $packer = null;

    /**
     * 雷达(请求类)
     */
    private $radar = null;

    private $direction = null;

    private $destination = null;

    private $destinationOrigin = null;

//    private


    /**
     * @param bool $origin
     * @return array
     */
    public function getParameters(bool $origin = false): array
    {
        return $origin ? $this->getParametersOrigin() : $this->parameters;
    }

    /**
     * @param array $parameters
     * @param bool $origin
     * @return $this
     */
    public function setParameters(array $parameters, bool $origin = false): static
    {
        if ($origin) {
            $this->setParametersOrigin($parameters);
        }

        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function mergeParameters(array $parameters): static
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        /*
         * 初始设置后
         * $this->parameters = ['out_trade_no' => 'abc123456', 'description' => 'subject-测试', 'amount' => ['total' => 1, 'currency' => 'CNY'], 'payer' => ['openid' => '123asdf321']];
         * 经过第二步 PayPlugin 插件处理后
         * $this->parameters = ['out_trade_no' => 'abc123456', 'description' => 'subject-测试', 'amount' => ['total' => 1, 'currency' => 'CNY'], 'payer' => ['openid' => '123asdf321'], '_method' => 'POST', '_url' => 'v3/pay/transactions/jsapi', 'notify_url' => 'xxx', 'appid' => '123456asdf', 'mchid' => '123456asdf'];
         * ##### 调整原执行步骤 -- 原步骤时先执行 AddPayloadBodyPlug 再执行 AddPayloadSignaturePlugin，现将两个步骤对调 #####
         * 经过第三步 AddPayloadSignaturePlugin 插件处理后
         * $this->parameters = ['out_trade_no' => 'abc123456', 'description' => 'subject-测试', 'amount' => ['total' => 1, 'currency' => 'CNY'], 'payer' => ['openid' => '123asdf321'], '_method' => 'POST', '_url' => 'v3/pay/transactions/jsapi', 'notify_url' => 'xxx', 'appid' => '123456asdf', 'mchid' => '123456asdf', '_authorization' => 'XXXXXXXX'];
         * 经过第四步 AddPayloadBodyPlug 插件处理后
         * $this->parameters = ['out_trade_no' => 'abc123456', 'description' => 'subject-测试', 'amount' => ['total' => 1, 'currency' => 'CNY'], 'payer' => ['openid' => '123asdf321'], '_method' => 'POST', '_url' => 'v3/pay/transactions/jsapi', 'notify_url' => 'xxx', 'appid' => '123456asdf', 'mchid' => '123456asdf', '_authorization' => 'XXXXXXXX', '_body' => ['out_trade_no' => 'abc123456', 'description' => 'subject-测试', 'amount' => ['total' => 1, 'currency' => 'CNY'], 'payer' => ['openid' => '123asdf321'], 'notify_url' => 'xxx', 'appid' => '123456asdf', 'mchid' => '123456asdf']];
         */

        return $this;
    }

    public function exceptPayload(mixed $key)
    {
        if (empty($this->payload)) {
            return $this;
        }

        $this->payload = $this->payload->except($key);

        return $this;
    }

    /**
     * @return null
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     */
    public function setPayload($payload)
    {
        if (!is_array($payload) || !$payload instanceof Collection) {
            throw;
        }

        if (is_array($payload)) {
            $payload = new Collection($payload);
        }

        $this->payload = $payload;

        return $this;
    }

//    public function mergePayload($payload)
//    {
//        if (!is_array($payload) || !$payload instanceof Collection) {
//            throw;
//        }
//
//        if (empty($this->payload)) {
//            $this->payload = new Collection();
//        }
//
//        $this->payload = $this->payload->merge($payload);
//
//        return $this;
//    }

    public function exceptPayload(mixed $key)
    {
        if (empty($this->payload)) {
            return $this;
        }

        $this->payload = $this->payload->except($key);

        return $this;
    }

    /**
     * @return null
     */
    public function getPacker()
    {
        return $this->packer;
    }

    /**
     * @param null $packer
     */
    public function setPacker($packer): void
    {
        $this->packer = $packer;
    }

    /**
     * @return null
     */
    public function getRadar()
    {
        return $this->radar;
    }

    /**
     * @param null $radar
     */
    public function setRadar($radar): void
    {
        $this->radar = $radar;
    }

    /**
     * @return null
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param null $direction
     */
    public function setDirection($direction): void
    {
        $this->direction = $direction;
    }

    public function getDestination(bool $origin = false)
    {
        return $origin ? $this->getDestinationOrigin() : $this->destination;
    }

    /**
     * @param $destination
     * @param bool $origin
     * @return $this
     */
    public function setDestination($destination, bool $origin = false): static
    {
        if ($origin) {
            $this->setDestinationOrigin(clone $destination);
        }

        $this->destination = $destination;

        return $this;
    }


    /**
     * @return array
     */
    private function getParametersOrigin(): array
    {
        return $this->parametersOrigin;
    }

    /**
     * @param $parametersOrigin
     * @return void
     */
    private function setParametersOrigin($parametersOrigin): void
    {
        $this->parametersOrigin = $parametersOrigin;
    }

    /**
     * @return null
     */
    private function getDestinationOrigin()
    {
        return $this->destinationOrigin;
    }

    /**
     * @param null $destinationOrigin
     */
    private function setDestinationOrigin($destinationOrigin): void
    {
        $this->destinationOrigin = $destinationOrigin;
    }
}