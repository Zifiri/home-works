<?php

namespace App;

class Utility
{

    public $baseURL;

    /**
     * @param $file
     * @param $data
     */
    protected function writeFile($file, $data)
    {
        $f = fopen($file, 'w');
        fwrite($f, $data);
        fclose($f);
    }

    protected function writeCache($file, $data)
    {
        $this->writeFile($file, json_encode($data));
    }

    protected function getCache($file)
    {
        if (is_file($file)) {
            return json_decode(file_get_contents($file), true);
        }
        return false;
    }

    protected function getData($data)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.40 Safari/537.36");


        /*if (preg_match('@https@', $data['url'])) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        }*/


        if (isset($data['post'])) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data["post"]);
        }

        $url = str_replace("__BASE_URL__", $this->baseURL, $data['url']);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


        if (isset($data['header'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $data['header']);
        }


        if (isset($data['cookie_file'])) {
            if (is_file($data['cookie_file']) === false) {
                curl_setopt($curl, CURLOPT_COOKIEJAR, $data['cookie_file']);
            } else {
                curl_setopt($curl, CURLOPT_COOKIEFILE, $data['cookie_file']);
            }
        }
        if (isset($data['ref'])) {
            curl_setopt($curl, CURLOPT_REFERER, $data['ref']);
        }
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);


        $source = curl_exec($curl);


        if ($source == false) {
            throw new Error($curl);
            //var_dump(curl_error($curl));

            //return false;
        }

        /*if (substr($source, 0, 2) == "\x1f\x8b") {
            $source = gzinflate(substr($source, 10, -8));
        }*/

        return $source;
    }


}