<?php
session_start();

$chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
$code = "";
for ($i = 0; $i < 6; $i++) {
    $code .= $chars[rand(0, strlen($chars) - 1)];
}
$_SESSION['captcha_code'] = $code;

$width = 160;
$height = 50;
$image = imagecreatetruecolor($width, $height);

$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$gray = imagecolorallocate($image, 200, 200, 200);

imagefilledrectangle($image, 0, 0, $width, $height, $white);

for ($i = 0; $i < 1000; $i++) {
    $noise_color = imagecolorallocate($image, rand(150, 230), rand(150, 230), rand(150, 230));
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noise_color);
}

for ($i = 0; $i < 8; $i++) {
    $line_color = imagecolorallocate($image, rand(100, 200), rand(100, 200), rand(100, 200));
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

for ($i = 0; $i < strlen($code); $i++) {
    $char_color = imagecolorallocate($image, rand(0, 100), rand(0, 100), rand(0, 100));
    $x = 20 + ($i * 22); 
    $y = rand(10, 25);
    
    imagestring($image, 5, $x, $y, $code[$i], $char_color);
}

for ($i = 0; $i < 3; $i++) {
    $arc_color = imagecolorallocate($image, rand(0, 150), rand(0, 150), rand(0, 150));
    imagearc($image, rand(0, $width), rand(0, $height), rand(50, 150), rand(30, 80), 0, 360, $arc_color);
}

header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>