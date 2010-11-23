<?php
error_reporting(E_ALL | E_STRICT);
$src = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src';
set_include_path(get_include_path() . $src);

require_once 'Wozozo/Zenra.php';
$appid  = getenv('YAHOO_APPID');
$params = array('appid' => $appid);
$zenra  = new \Wozozo\Zenra($params);
var_dump($zenra->zenrize('お腹が空きました'));
// お腹が全裸で空きました

$params = array(
    'appid'    => $appid,
    'position' => '名詞',
    'text'     => '夜の'
);
$zenra = new \Wozozo\Zenra($params);
var_dump($zenra->zenrize('お腹が空いたのでスパゲッティが食べたい'));
// 夜のお腹が空いたので夜のスパゲッティが食べたい
