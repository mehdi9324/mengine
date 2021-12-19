<?php

namespace StingBo\Mengine\Core;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class Order
{
    /**
     * 唯一标识，可以是用户id.
     */
    public $uuid;

    /**
     * 单据id.
     */
    public $oid;

    /**
     * 符号.
     */
    public $symbol;

    /**
     * 买卖.
     */
    public $transaction;

    /**
     * 数量.
     */
    public $volume;

    /**
     * 价格.
     */
    public $price;

    /**
     * 精度.
     */
    public $accuracy;

    /**
     * 节点.
     */
    public $node;

    public $is_first = false;
    public $is_last = false;

    /**
     * 节点前一个.
     */
    public $prev_node;

    /**
     * 节点后一个.
     */
    public $next_node;

    /**
     * 节点链.
     */
    public $node_link;

    /**
     * hash对比池标识.
     */
    public $order_hash_key;
    public $order_hash_field;

    /**
     * zset委托列表.
     */
    public $order_list_zset_key;

    /**
     * hash委托深度.
     */
    public $order_depth_hash_key;
    public $order_depth_hash_field;

    public function __construct($uuid, $oid, $symbol, $transaction, $volume, $price)
    {
        $this->setSymbol($symbol);
        $this->setAccuracy();
        $this->setUuid($uuid);
        $this->setOid($oid);
        $this->setTransaction($transaction);
        $this->setVolume($volume);
        $this->setPrice($price);
        $this->setOrderHashKey();
        $this->setListZsetKey();
        $this->setDepthHashKey();
        $this->setNode();
        $this->setNodeLink();
    }

    /**
     * set uuid.
     */
    public function setUuid($uuid)
    {
        if (!$uuid) {
            throw new InvalidArgumentException(__METHOD__.' expects argument uuid is not empty.');
        }

        $this->uuid = $uuid;

        return $this;
    }

    /**
     * set oid.
     */
    public function setOid($oid)
    {
        if (!$oid) {
            throw new InvalidArgumentException(__METHOD__.' expects argument oid is not empty.');
        }

        $this->oid = $oid;

        return $this;
    }

    /**
     * set symbol.
     */
    public function setSymbol($symbol)
    {
        if (!$symbol) {
            throw new InvalidArgumentException(__METHOD__.' expects argument symbol is not empty.');
        }

        $this->symbol = $symbol;

        return $this;
    }

    /**
     * set transaction.
     */
    public function setTransaction($transaction)
    {
        if (!in_array($transaction, config('mengine.mengine.transaction'))) {
            throw new InvalidArgumentException(__METHOD__.' expects argument transaction to be a valid type of [config.mengine.transaction].');
        }

        $this->transaction = $transaction;

        return $this;
    }

    /**
     * set volume.
     */
    public function setVolume($volume)
    {
        if (floatval(number_format($volume,8)) <= 0) {
            throw new InvalidArgumentException(__METHOD__.' expects argument volume greater than 0.');
        }

        /**
         * mehdi
         * There is a problem here that the package changed
         *
         * The main package code
         * $this->volume = bcmul($volume, bcpow(10, $this->accuracy));
         */
        $volume1 = number_format($volume, 8, '.', '');
        $this->volume = bcmul($volume1, bcpow(10, $this->accuracy));

        return $this;
    }

    /**
     * set price.
     */
    public function setPrice($price)
    {
        if (floatval($price) <= 0) {
            throw new InvalidArgumentException(__METHOD__.' expects argument price greater than 0.');
        }

        $this->price = bcmul($price, bcpow(10, $this->accuracy));

        return $this;
    }

    /**
     * set accuracy.
     */
    public function setAccuracy()
    {
        $accuracy = config("mengine.mengine.{$this->symbol}_accuracy") ?? config('mengine.mengine.accuracy');
        if (floor(0) !== (floatval($accuracy) - $accuracy)) {
            throw new InvalidArgumentException(__METHOD__.' expects argument config.mengine.mengine.accuracy is a positive integer.');
        }

        $this->accuracy = $accuracy;

        return $this;
    }

    /**
     * 委托标识池field.
     */
    public function setOrderHashKey()
    {
        $this->order_hash_key = $this->symbol.':comparison';
        $this->order_hash_field = $this->symbol.':'.$this->uuid.':'.$this->oid;

        return $this;
    }

    /**
     * 委托列表.
     */
    public function setListZsetKey()
    {
        $this->order_list_zset_key = $this->symbol.':'.$this->transaction;

        return $this;
    }

    /**
     * 深度.
     */
    public function setDepthHashKey()
    {
        $this->order_depth_hash_key = $this->symbol.':depth';
        $this->order_depth_hash_field = $this->symbol.':depth:'.$this->price;

        return $this;
    }

    /**
     * hash模拟node.
     */
    public function setNode()
    {
        $this->node = $this->symbol.':node:'.$this->oid;

        return $this;
    }

    /**
     * hash模拟Link.
     */
    public function setNodeLink()
    {
        $this->node_link = $this->symbol.':link:'.$this->price;

        return $this;
    }
}
