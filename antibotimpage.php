<?php
//create image of size width=200 height=75, false on failure
$xSize = 200;
$ySize = 75;

//random code
$code = 'unknown';
$spacePerChar = $xSize / (strlen($code) + 1);
$my_img = imagecreatetruecolor( $xSize, $ySize );
echo '<br>response from antibot file';

//colours (r,g,b) must be allocated before use, false on failure
$background = imagecolorallocate( $my_img, 255, 255, 255 );
$border = imagecolorallocate( $my_img, 128, 128, 128 );
$colours[0] = imagecolorallocate( $my_img, 128, 64, 192 );
$colours[1] = imagecolorallocate( $my_img, 192, 64, 128 );
$colours[2] = imagecolorallocate( $my_img, 108, 192, 64 );

//fill rectangle
imagefilledrectangle($my_img, 0, 0, $xSize - 1, $ySize - 1, $border);
imagefilledrectangle($my_img, 1, 1, $xSize - 2, $ySize - 2, $background);

//drawing text
//for($i = 0; $i < strlen($code);$i++){
	//$colour = $colours[$i % count($colours)];
//	imagettftext($my_img,//imagettftext($my_img,
//				 28,
//				 0,
//				 ($i + 0.3)*$spacePerChar,
//				 50,
//				 $colours[0],
//				 '/arial.ttf',//'Arial-Regular.ttf',//arial.ttf
//				 'a');
}

//why won't this bit work????????????????????????????????????????????????????????/
for($i = 0; $i < strlen($code);$i++){
	$colour = $colours[$i % count($colours)];
	imagefttext($my_img,//imagettftext($my_img,
				 28 + rand(0,8),
				 -20 + rand(0,40),
				 ($i + 0.3)*$spacePerChar,
				 50 + rand(0,10),
				 $colour,
				 'Vera.ttf',//arial.ttf
				 $code[$i]);
}
//add distorsions to image
//imageantialias($my_img,true);
//for($i = 0;$i < 1000;$i++){
//	//start coords
//	$x0 = rand(5, $xSize - 5);
//	$y0 = rand(5, $ySize - 5);
//	//end coords
//	$x1 = $x0 - 4 + rand(0,8);
//	$y1 = $y0 - 4 + rand(0,8);
//	//draw line
//	imageline($my_img, $x0, $y0, $x1, $y1, $colours[rand(0, count($colours) - 1)]);
//}


//tells browser that following data is bytes of a PNG image
header( "Content-type: image/png" );//image/jpeg or image/gif

//creates the image from $my_img, without second arg output sent to browser
imagepng( $my_img );//imagegif() or imagejpeg()

imagecolordeallocate( $colours );
imagecolordeallocate( $border );
imagecolordeallocate( $background );
imagedestroy( $my_img );
gd_info();
?>