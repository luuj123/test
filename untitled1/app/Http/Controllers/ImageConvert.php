<?php
/**
 * Created by JetBrains PhpStorm.
 * User: USER
 * Date: 14. 2. 10
 * Time: 오후 1:47
 * To change this template use File | Settings | File Templates.
 */

namespace App\Http\Controllers;

//use Illuminate\Http\Request;
use Illuminate\Http\Response;

//
//use App\Http\Requests;
//
use App\Http\Controllers\Controller;
use App\UserRegisters;
//use App\EstimateShort;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests;

class ImageConvert extends Controller{

    static private $file = 0;
    static private $image_width;
    static private $image_height;
    static private $width;
    static private $height;
    static private $ext;
    static private $types = array('','gif','jpeg','png','swf');
    static private $quality = 80;
    static private $top = 0;
    static private $left = 0;
    static private $crop = false;
    static private $type;
    static private $dir;
    static private $name;

   static function Image($name='') {
        self::$file = $name;
        $info = getimagesize($name);
        self::$image_width = $info[0];
        self::$image_height = $info[1];
        self::$type = self::$types[$info[2]];
        $info = pathinfo($name);
        self::$dir = $info['dirname'];
        self::$name = str_replace('.'.$info['extension'], '', $info['basename']);
        self::$ext = $info['extension'];
    }

    function dir( $dir='') {
        if(!$dir) return self::$dir;
        self::$dir = $dir;
    }

    function name($name='') {
        if(!$name) return self::$name;
        self::$name = $name;
    }

    function width($width='') {
        self::$width = $width;
    }

    function height($height='') {
        self::$height = $height;
    }

    static function resize($percentage=50) {
        if(self::$crop) {
            self::$crop = false;
            self::$width = round(self::$width*($percentage/100));
            self::$height = round(self::$height*($percentage/100));
            self::$image_width = round(self::$width/($percentage/100));
            self::$image_height = round(self::$height/($percentage/100));
        } else {
            self::$width = round(self::$image_width*($percentage/100));
            self::$height = round(self::$image_height*($percentage/100));
        }

    }

    function crop($top=0, $left=0) {
        self::$crop = true;
        self::$top = $top;
        self::$left = $left;
    }

    function quality($quality=80) {
        self::$quality = $quality;
    }

    function show() {
        self::save(true);
    }

    static function save($show=false) {

        if($show) @header('Content-Type: image/'.self::$type);

        if(!self::$width && !self::$height) {
            self::$width = self::$image_width;
            self::$height = self::$image_height;
        } elseif (is_numeric(self::$width) && empty(self::$height)) {
            self::$height = round(self::$width/(self::$image_width/self::$image_height));
        } elseif (is_numeric(self::$height) && empty(self::$width)) {
            self::$width = round(self::$height/(self::$image_height/self::$image_width));
        } else {
            if(self::$width<=self::$height) {
                $height = round(self::$width/(self::$image_width/self::$image_height));
                if($height!=self::$height) {
                    $percentage = (self::$image_height*100)/$height;
                    self::$image_height = round(self::$height*($percentage/100));
                }
            } else {
                $width = round(self::$height/(self::$image_height/self::$image_width));
                if($width!=self::$width) {
                    $percentage = (self::$image_width*100)/$width;
                    self::$image_width = round(self::$width*($percentage/100));
                }
            }
        }

        if(self::$crop) {
            self::$image_width = self::$width;
            self::$image_height = self::$height;
        }

        if(self::$type=='jpeg') $image = imagecreatefromjpeg(self::$file);
        if(self::$type=='png') $image = imagecreatefrompng(self::$file);
        if(self::$type=='gif') $image = imagecreatefromgif(self::$file);

        $new_image = imagecreatetruecolor(self::$width, self::$height);
        imagecopyresampled($new_image, $image, 0, 0, self::$top, self::$left, self::$width, self::$height, self::$image_width, self::$image_height);

        $name = $show ? null: self::$dir.DIRECTORY_SEPARATOR.self::$name.'.'.self::$ext;
        if(self::$type=='jpeg') imagejpeg($new_image, $name, self::$quality);
        if(self::$type=='png') imagepng($new_image, $name);
        if(self::$type=='gif') imagegif($new_image, $name);
        if(self::$type=='gif') imagegif($new_image, $name);

        imagedestroy($image);
        imagedestroy($new_image);

    }

}