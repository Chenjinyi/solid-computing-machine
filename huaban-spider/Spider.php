<?php
/**
 * Created by PhpStorm.
 * User: minec
 * Date: 2017/11/6
 * Time: 15:43
 */

//爬取未登录可获得的照片

class spiderClass
{
    public function errorBack($message)
    {
        print_r($message);
        return false;
    }//错误返回

    public function argvIsTrue($argv,$argvNumber) //argv是否存在
    {
        if (empty($argv[$argvNumber])){
            return $this->errorBack('Mode Null');
        }
        return true;
    }

    protected $header = [
        "Host:huaban.com",
        "Connection: keep-alive",
        "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
        "Upgrade-Insecure-Requests: 1",
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
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        if (empty($result = curl_exec($curl))){
            print_r("Curl Website Error");
            return false;
        }
        curl_close($curl);
        return $result;
    }//Curl

}

$spider = new spiderClass();
$dirName = date('m-d').'-'.$argv[1];

if ($search  = $spider->argvIsTrue($argv,1)) {
    $url = "http://huaban.com/search/?q=" . $search;
    $index = $spider->curlWebsite($url);

    preg_match_all("/key.{40,60}\"/",$index,$imageUrls);
    $imageUrls = str_replace(["key","\"",",",":"],'',$imageUrls[0]);

    if(touch($dirName)){
        for ($i=0;$i<=count($imageUrls)-1;$i++){
            $url= "http://img.hb.aicdn.com/".$imageUrls[$i].PHP_EOL;
            file_put_contents($dirName ,$url,FILE_APPEND);
            print_r($url);
        }
    }
}