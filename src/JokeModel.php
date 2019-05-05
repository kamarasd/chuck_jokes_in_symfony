<?php

namespace App;

//define spreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class JokeModel
{
    public static function getJokes($num = 1,$category=array(),$b_url) {
        $joke_url="/jokes/random/{$num}";
        $temp = "";

        if(!empty($category)) {
            $get = array(
                'limitTo' => "",
                'exclude' => ""
            );

            if(!empty($category)) {

                foreach ($category as $ctg) {
                    $temp.="{$ctg},";
                    $get['limitTo'] = "[{$temp}]";
                }
            }

            $joke_url.= "?limitTo={$get['limitTo']}";
        }

        $url = $b_url.$joke_url;
        return self::callJoke($url);
    }

    public static function callJoke($url,$get = array()) {
        $curl = curl_init();

        if(empty($url)) return array('error', 'No URL defined');

        if(!empty($get)) {
            $url.= "?";
            foreach ($get as $key=>$value) {
                $key = rawurldecode($key);
                $value = rawurldecode($value);
                $url.="{$key}={$value}";
            }
        }

        curl_setopt($curl, CURLOPT_USERAGENT, "jokes");
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_URL, $url);

        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($code == 200 && !empty($ret)) {
            $ret = json_decode($ret, true);

            if(empty($ret)) {
                return array('error'=> 'No jokes to write');
            }
            else {
                return self::WriteJoke($ret);
            }
        }
        else {
            return array('error'=>'Problem occurred with gathering the jokes');
        }

    }

    protected static function WriteJoke($ret) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $size = sizeof($ret['value']);

        $sheet -> setCellValue('A1', 'ID');
        $sheet -> setCellValue('B1', 'Joke');
        $sheet -> setCellValue('C1', 'Category');

        $i=2;
        $j=0;

        for($j;$j<$size;$j++) {
            $sheet -> setCellValue('A'.$i, $ret['value'][$j]['id']);
            $sheet -> setCellValue('B'.$i, htmlspecialchars_decode($ret['value'][$j]['joke']));
            if(!empty($ret['value'][$j]['categories'])) {
                foreach($ret['value'][$j]['categories'] as $cat) {
                    $sheet -> setCellValue('C'.$i, $cat);
                }
            }

            $i++;
        }

        $writer = new Xlsx($spreadsheet);
        $date = Date('m-d_his');
        $fileName = 'norris_jokes.'.$date.'.xlsx';
        $writer->save($fileName);

        return array('success'=>'You could open the Excel file from here: '.getcwd().'\ '.$fileName);
    }

}