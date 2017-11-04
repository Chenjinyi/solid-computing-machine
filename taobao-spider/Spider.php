<?php
/**
 * Created by PhpStorm.
 * User: minec
 * Date: 2017/11/3
 * Time: 23:40
 */

error_reporting(E_ERROR);

class Spider{
    protected $header = [
        "content-type: application/x-www-form-urlencoded;",
        "charset=UTF-8",
        "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
        "Accept-Language:zh-CN,zh;q=0.8",
        "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36"
    ];//Header

    public function curlWebsite($url)
    {
        if (empty($url)) {
            print_r("Url Null");
            return false;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_AUTOREFERER,true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        if (empty($result = curl_exec($curl))){
            print_r("Curl Website Error");
            return false;
        }
        curl_close($curl);
        return $result;
    }//Curl
}
//淘宝店铺跳转Demo
// store.taobao.com/shop/view_shop.htm?user_number_id= 751804226

if (!empty($argv[1])) {
    $urls = "https://s.taobao.com/search?q=" . $argv[1] . "&imgfile=&commend=all&ssid=s5-e&search_type=item&sourceId=tb.index&spm=a21bo.2017.201856-taobao-item.1&ie=utf8&initiative_id=tbindexz_20170306";
}else{
    print_r("参数非法|未指定搜索参数");
    die();
}//获取搜索关键词

$spider = new Spider();
$index  = $spider->curlWebsite($urls);//获取搜索页

preg_match_all("/user_id.{3}\d{5,18}/",$index,$goodsUrls);
$goodsUrls = $goodsUrls[0];
$goodsUrls = str_replace("user_id\":\"",'',$goodsUrls);//获取商家ID
//print_r($goodsUrls);

for ($i=0;$i<=count($goodsUrls)-1;$i++){
    $shopUrl = "https://store.taobao.com/shop/view_shop.htm?user_number_id=".$goodsUrls[$i];
    $shop = $spider->curlWebsite($shopUrl);//获取商店
    $shop=mb_convert_encoding($shop,'utf8','GB2312');//编码转换

    //淘宝店信息获取
    preg_match('/TotalBailAmount.{2}\d{1,10}/',$shop,$totalBailAmount);
    $totalBailAmount= str_replace("TotalBailAmount\">",'',$totalBailAmount[0]);//获取保证金 天猫店不存在

    /*
     *  <a class="shop-name" href="//shop148748370.taobao.com"><span>华斯精品服饰</span></a> 淘宝店
     *   <a class="shop-name" href="//handaiweizm.tmall.com"  rel="nofollow" ><span>翰代维佐慕专卖店</span></a> 天猫店
     */

    preg_match('/shop-name.{8}.*".{7}.*s/',$shop,$shopName);
    $shopName = str_replace("shop-name\" href=\"",'https:',$shopName[0]);//店铺URL NAME替换
    $shopName = str_replace("\"",'',$shopName);
    $shopName = str_replace(['>','<','/s'],'',$shopName);
    $shopName = str_replace('span',',',$shopName);
    $shopName = str_replace('  rel=nofollow','',$shopName);//天猫店 替换
    $shopName = explode(',',$shopName);

    /* 分数显示区别
     *  <em class="count" title="4.85198分">4.8</em> 天猫
     *   <li>描述相符<em class="count"
                                                               title="4.72937分">4.7</em> 淘宝
     */

    preg_match_all('/title.{2}\d.\d{1}/',$shop,$fraction); //获取分数
    $fraction = str_replace("title=\"",'',$fraction[0]);

    print_r(
        '店铺名: '.$shopName[1].'    店铺链接: '.$shopName[0]."    保证金(淘宝店): ".$totalBailAmount."   描述相符: ".$fraction[0]."   服务态度: ".$fraction[1]."   物流速度: ".$fraction[2].PHP_EOL
    );

}