<?php
namespace restphp\validate\image;

/**
 * 图像验证码.
 * @package restphp\validate\image
 */
class RestValidateImage {
    /**
     * 输出验证码图像.
     * @param $strInfo string 验证码信息.
     */
    public static function responseImage($strInfo, $width = 120, $height = 20) {
        header("content-type:image/png");
        $numberImg = @imagecreate($width, $height);
        imagecolorallocate($numberImg, 220, 250, 250);
        $strInfoLen = strlen($strInfo);
        for ($i = 0; $i < strlen($strInfo); $i++)
        {
            $font = mt_rand(5, 7);
            $x = mt_rand(1, $strInfoLen) + $width * $i / $strInfoLen;
            $y = mt_rand(1, $height / 4) ;
            $color = imagecolorallocate($numberImg, mt_rand(100, 255), mt_rand(0, 110), mt_rand(0, 100));
            imagestring($numberImg, $font, $x, $y, $strInfo[$i], $color);
        }
        for ($i=0;$i<200;$i++){
            imagesetpixel($numberImg,rand() % 100,rand() % 30, $color);
        }
        imagepng($numberImg);
        imagedestroy($numberImg);
    }
}