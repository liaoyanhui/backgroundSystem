<?php

namespace app\admin;

class Constants
{
  //短信验证码的事件
  public const SMS_EVENT_LOGIN = 'admin_login';
  public const SMS_EVENT_REGISTER = 'admin_register';
  public const SMS_EVENT_CHANGE_PASSWORD = 'admin_change_password';

  // 关联关系
  public const RELATED_TYPE_SUPPLIER = 1; //供应商
  public const RELATED_TYPE_BUSINESS_COMPANY = 2; //业务公司
  public const RELATED_TYPE_CONTRACTING_COMPANY = 3; //签约单位
  public const RELATED_TYPE_PURCHASER = 4; //采购商

  // 结算关系
  public const RELATIONSHIP_B_S = 1; //业务公司与供应商
  public const RELATIONSHIP_C_B = 2; //签约公司与业务公司
  public const RELATIONSHIP_P_C = 3; //平台公司与签约公司

  // 发票类型
  public const INVOICE_TYPE_PLAIN = 1; //普票
  public const INVOICE_TYPE_SPECIAL_3 = 2; //专票-3%
  public const INVOICE_TYPE_SPECIAL_13 = 3; //专票-13%

  // 订单状态
  public const ORDER_STATUS_NO_AUDIT = 1; //未审核
  public const ORDER_STATUS_WAIT_DELIVERY = 2; //待发货
  public const ORDER_STATUS_DELIVERED = 3; //已发货
  public const ORDER_STATUS_FINISHED = 4; //已完成

  // 结算方式
  public const SETTLEMENT_TYPE_ORDER = 1; // 订单结算
  public const SETTLEMENT_TYPE_MONTH = 2; // 月结算
  public const SETTLEMENT_TYPE_WEEK = 3; // 周结算
  public const SETTLEMENT_TYPE_BACK = 4; // 背靠背结算


  static $orderStatus = [
    self::ORDER_STATUS_NO_AUDIT => '未审核',
    self::ORDER_STATUS_WAIT_DELIVERY => '待发货',
    self::ORDER_STATUS_DELIVERED => '已发货',
    self::ORDER_STATUS_FINISHED => '已完成'
  ];

  // 子订单状态
  public const SUB_ORDER_STATUS_WAIT_INVOICE = 1; //待开票
  public const SUB_ORDER_STATUS_PARTIALLY_INVOICE = 2; //部分开票
  public const SUB_ORDER_STATUS_WAIT_PAY = 3; //待付款
  public const SUB_ORDER_STATUS_PARTIALLY_PAY = 4; //部分付款
  public const SUB_ORDER_STATUS_FINISHED = 5; //已完成

  static $subOrderStatus = [
    self::SUB_ORDER_STATUS_WAIT_INVOICE => '待开票',
    self::SUB_ORDER_STATUS_PARTIALLY_INVOICE => '部分开票',
    self::SUB_ORDER_STATUS_WAIT_PAY => '待付款',
    self::SUB_ORDER_STATUS_PARTIALLY_PAY => '部分付款',
    self::SUB_ORDER_STATUS_FINISHED => '已完成',
  ];

  // 发票单状态
  public const INVOICE_STATUS_WAIT_INVOICE = 1; //待开票
  public const INVOICE_STATUS_PARTIALLY_INVOICE = 2; //部分开票
  public const INVOICE_STATUS_FINISHED = 3; //已完成

  static $invoiceStatus = [
    self::INVOICE_STATUS_WAIT_INVOICE => '待开票',
    self::INVOICE_STATUS_PARTIALLY_INVOICE => '部分开票',
    self::INVOICE_STATUS_FINISHED => '已完成',
  ];

  // 结算单状态
  public const STEELEMENT_STATUS_WAIT_SETTLEMENT = 1; //待开票
  public const STEELEMENT_STATUS_PARTIALLY_STEELEMENT = 2; //部分开票
  public const STEELEMENT_STATUS_FINISHED = 3; //已完成

  static $settlementStatus = [
    self::STEELEMENT_STATUS_WAIT_SETTLEMENT => '待回款',
    self::STEELEMENT_STATUS_PARTIALLY_STEELEMENT => '部分回款',
    self::STEELEMENT_STATUS_FINISHED => '已完成',
  ];

  // 送货方式
  public const DELIVER_WAY = 1; // 货运

  static $deliverWay = [
    self::DELIVER_WAY => '货运'
  ];

  // 采购商等级
  public const PURCHASER_LEVEL_ONE = 1; //一级
  public const PURCHASER_LEVEL_TWO = 2; //二级
  public const PURCHASER_LEVEL_THREE = 3; //三级
  public const PURCHASER_LEVEL_FOUR = 4; //四级
  public const PURCHASER_LEVEL_FIVE = 5; //五级

  static $purchaserLevel = [
    self::PURCHASER_LEVEL_ONE => '一级',
    self::PURCHASER_LEVEL_TWO => '二级',
    self::PURCHASER_LEVEL_THREE => '三级',
    self::PURCHASER_LEVEL_FOUR => '四级',
    self::PURCHASER_LEVEL_FIVE => '五级',
  ];

  /**
   * 获取订单状态的文本
   */
  public static function getOrderStatusText($status): string
  {
    return self::$orderStatus[$status];
  }

  /**
   * 获取子订单状态的文本
   */
  public static function getSubOrderStatus($status): string
  {
    return self::$subOrderStatus[$status];
  }

  /**
   * 获取发票单状态的文本
   */
  public static function getInvoiceStatus($status): string
  {
    return self::$invoiceStatus[$status];
  }
}
