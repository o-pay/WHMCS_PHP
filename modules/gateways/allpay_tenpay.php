<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/allpay/allpay.php';

function allpay_tenpay_MetaData() {
    return array(
        'DisplayName' => '歐付寶 - 財付通',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => false,
        'TokenisedStorage' => false,
    );
}

function allpay_tenpay_config() {
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => '財付通',
        ),
        'MerchantID' => array(
            'FriendlyName' => '會員編號',
            'Type' => 'text',
            'Size' => '7',
            'Default' => '',
            'Description' => '歐付寶會員編號。',
        ),
        'HashKey' => array(
            'FriendlyName' => 'HashKey',
            'Type' => 'password',
            'Size' => '16',
            'Default' => '',
            'Description' => '於廠商管理後台->系統開發管理->系統介接設定中取得',
        ),
        'HashIV' => array(
            'FriendlyName' => 'HashIV',
            'Type' => 'password',
            'Size' => '16',
            'Default' => '',
            'Description' => '於廠商管理後台->系統開發管理->系統介接設定中取得',
        ),
        'ExpireTime' => array(
            'FriendlyName' => '付款截止時間',
            'Type' => 'text',
            'Size' => '3',
            'Default' => '72',
            'Description' => '小時 ( ≤ 72 )',
        ),
        'InvoicePrefix' => array(
            'FriendlyName' => '帳單前綴',
            'Type' => 'text',
            'Default' => '',
            'Description' => '選填（只能為數字、英文，且與帳單 ID 合併總字數不能超過 20）',
            'Size' => '5',
        ),
        'testMode' => array(
            'FriendlyName' => '測試模式',
            'Type' => 'yesno',
            'Description' => '測試模式',
        ),
    );
}

function allpay_tenpay_link($params) {

    # Invoice Variables
    $TimeStamp = time();
    $TradeNo = $params['InvoicePrefix'].$TimeStamp.$params['invoiceid'];
    $amount = $params['amount']; # Format: ##.##
    $TotalAmount = round($amount); # Format: ##

    # System Variables
    $systemurl = $params['systemurl'];

    # 交易設定
    if (!(int)$params['ExpireTime']) {
        $params['ExpireTime'] = 72; //預設72
    }
    $ExpireTime = date('Y/m/d H:i:s', strtotime('+'.$params['ExpireTime'].' hours'));

    $transaction = new AllPay_Pay('Tenpay');

    # 是否為測試模式
    if ($params['testMode'] == 'on') {
        $transaction->setTestMode();
    } else {
        $transaction->MerchantID = $params['MerchantID'];
        $transaction->HashKey = $params['HashKey'];
        $transaction->HashIV  = $params['HashIV'];
    }

    $transaction->MerchantTradeNo = $TradeNo;
    $transaction->TotalAmount = $TotalAmount;
    $transaction->TradeDesc = $params['description'];
    $transaction->ItemName = $params['description'];
    $transaction->ReturnURL = $systemurl.'/modules/gateways/callback/allpay_tenpay.php';
    $transaction->ClientBackURL = $params['returnurl'];
    $transaction->ExpireTime = $ExpireTime;

    return $transaction->GetHTML($params['langpaynow']);
}
