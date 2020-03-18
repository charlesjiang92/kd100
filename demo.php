<?php
include_once './vendor/autoload.php';
use Utils\Kd100Utils;

try {
    $kd = new Kd100Utils();

    //其他快递
    $re = $kd->find("快递单号");
    var_dump($re);

    //顺丰
    $re = $kd->find("快递单号", "收件人手机号码后4位");
    var_dump($re);

} catch (\Exception $e) {
    var_dump($e->getMessage());
}