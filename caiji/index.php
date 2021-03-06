<?php
$load = 5;
header("Content-type:text/html;charset=utf-8");
date_default_timezone_set("Asia/Shanghai");
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
include_once "../Public/config.php";
require "jiesuan.php";
if ($_GET['t'] == 'test') {
    SSC_jiesuan();
    exit;
}
$url = "http://api.woaizy.com/chatkj.php";
$json = file_get_contents($url);
$jsondata = json_decode($json);
$typearr['bjpk10'] = 1;
$typearr['mlaft'] = 2;
$typearr['cqssc'] = 3;
$typearr['bjkl8'] = 4;
$typearr['cakeno'] = 5;
$typearr['jsmt'] = 6;
$typearr['jssc'] = 7;
$typearr['jsssc'] = 8;
$typearr['jsk3'] = 9;
$typearr['baccarat'] = 10;

$jsondata = $jsondata->data;
foreach ($jsondata as $i) {
    $code = $i->code;
    if (!isset($typearr[$code])) continue;
    $opencode = $i->open_result;
    $qihao = $i->open_phase;
    $opentime = $i->load_time;
    $openindex = $i->open_index;
    $next_term = $i->next_phase;
    $nexttime = $i->next_time;
    $typeid = $typearr[$code];
    $topcode = db_query("select `term` from `fn_open` where `type`=$typeid order by `term` desc limit 1");
    $topcode = db_fetch_array();
    if ($code == 'cakeno') {
        $next_term = (int)$qihao + 1;
        $yy = explode(" ", $opentime);
        $yy = $yy[0];
        $tt = date('H:i:s', strtotime($opentime));
        $tt2 = explode(":", $tt);
        $ss = (int)$tt2[2];
        if ($ss != 0 || $ss != 30) {
            if ($ss < 30) {
                $ss2 = '00';
                $sstime = $yy . " " . str_replace($ss, $ss2, $tt);
                $nexttime = date('Y-m-d H:i:s', strtotime("$sstime +3 minute +30 seconds"));
            } elseif ($ss > 30) {
                $ss2 = '30';
                $sstime = $yy . " " . str_replace($ss, $ss2, $tt);
                $nexttime = date('Y-m-d H:i:s', strtotime("$sstime +3 minute +30 seconds"));
            }
        }
    }
    if ($topcode[0] <> $qihao) {
        insert_query('fn_open', array('term' => $qihao, 'code' => $opencode, 'time' => $opentime, 'type' => $typeid, 'next_term' => $next_term, 'next_time' => $nexttime));
        if ($code == 'bjkl8' || $code == 'cakeno') {
            PC_jiesuan($qihao); //加拿大28  幸运28
        }
        if ($code == 'jsmt') {
            MT_jiesuan($qihao); //极速摩托
        }
        if ($code == 'cqssc') {
            SSC_jiesuan($qihao); //重庆时时彩
        }
        if ($code == 'jsssc') {
            JSSSC_jiesuan($qihao); //极速时时彩
        }
        if ($code == 'jssc') {
            JSSC_jiesuan($qihao); //极速赛车
        }
        if ($code == 'bjpk10' || $code == 'mlaft') {
            jiesuan($qihao); //幸运飞艇  北京赛车
        }
        if ($code == 'jsk3') {
            K3_jiesuan($qihao); //快三娱乐
        }
        kaichat($code, $next_term);
        echo "更新 $code 成功！<br>";
    } else {
        echo "等待 $code 刷新<br>";
    }
}

//zepto 2017-10-13
echo "系统当前时间戳为 ";
echo "";
echo time();
//<!--JS 页面自动刷新 -->
echo("<script type=\"text/javascript\">");
echo("function fresh_page()");
echo("{");
echo("window.location.reload();");
echo("}");
echo("setTimeout('fresh_page()',5000);");
echo("</script>");
?>