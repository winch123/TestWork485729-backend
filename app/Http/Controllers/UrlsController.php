<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UrlsList;

class UrlsController extends Controller
{
    function createShortLink(Request $request) {
        sleep(1.5);

        $request->validate([
            'link' => 'required|url',
        ]);

        //$link = 'https://google.com';
        $link = $request->input('link');

        if ($u = UrlsList::where('url', '=', $link)->get()->first()) {
            $code = $u['code'];
        }
        else {
            if (!$this->isUrlExist($link)) {
                $err_text = 'URL didn`t return code 200';
                return response(['message' => $err_text, 'errors' => ['link' => [$err_text]]], 422);
            }

            $url = new UrlsList;
            $url->url = $link;
            do {
                $url->code = $code = $this->genpass(4);
            } while (UrlsList::where('code', '=', $code)->exists());

            $url->save();
        }

        return ['code' => $code];
    }

    private function isUrlExist($url) {
        $c = curl_init();
        curl_setopt($c,CURLOPT_URL,$url);
        curl_setopt($c,CURLOPT_HEADER,1);
        curl_setopt($c,CURLOPT_NOBODY,1);
        curl_setopt($c,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($c,CURLOPT_FRESH_CONNECT,1);
        curl_exec($c);
        $httpcode = curl_getinfo($c, CURLINFO_HTTP_CODE);

        return $httpcode == 200;
    }

    private function genpass($len) {
        $arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
                '1','2','3','4','5','6','7','8','9','0',
                'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
        $pass = '';
        for($i = 0; $i < $len; $i++) {
            $pass .= $arr[rand(0, count($arr)-1)];
        }

        return $pass;
    }

    function redirect(Request $request, $code) {
        if ($u = UrlsList::where('code', '=', $code)->get()->first()) {
            header('Location:'.$u['url']);
            exit;
        }
        else {
            return 'This link not exist';
        }
    }
}
