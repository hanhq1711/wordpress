<?php
//使用PHP的GD库生成验证码

//1 创建一个验证码 4位数字
$code =rand(1000,9999);
//2 用SESSION存起来
session_start();
$_SESSION['um_captcha'] = $code;

header("Content-type: image/PNG");  //添加头部信息,告知此页面输出一张图片

//3 开始绘制图片 imagecreate
// resource imagecreate ( int $x_size , int $y_size )
$im = imagecreate(60,32);

//4 生成所需要的颜色  imagecolorallocate
// int imagecolorallocate ( resource $image , int $red , int $green , int $blue )
$blue = imagecolorallocate($im,162,234,250);
$green = imagecolorallocate($im,29,78,36);

//5 填充背景颜色
// bool imagefill ( resource $image , int $x , int $y , int $color )
imagefill($im,0,0,$blue);

//6 画2条随机生成的线, 起到干扰作用
// 1) imagesetstyle 设定画线的风格
// bool imagesetstyle ( resource $image , array $style )
// 画一条实线 5像素蓝 5像素橙
$style = array($blue,$blue,$blue,$blue,$blue,$green,$green,$green,$green,$green);
imagesetstyle($im,$style);
// 2) imageline  画一条线段
// bool imageline ( resource $image , int $x1 , int $y1 , int $x2 , int $y2 , int $color )
$start_y = rand(0,32); // 起点的Y轴值
$finish_y = rand(0,32);  //终点的Y轴值
$start_y2 = rand(0,32); // 2起点的Y轴值
$finish_y2 = rand(0,32);  //2终点的Y轴值

imageline($im,0,$start_y,60,$finish_y,$green);  //用橙色画一条线
imageline($im,0,$start_y2,60,$finish_y2,$green);  //用橙色画第二条线
imageline($im,0,$finish_y2,60,$start_y2,$green);  //用橙色画第二条线
imageline($im,0,$finish_y,60,$start_y,$green);  //用橙色画第二条线


//7 随机生成黑色的点
// imagesetpixel 画一个单一像素
// bool imagesetpixel ( resource $image , int $x , int $y , int $color )
//生成60个随机点
for($i=1;$i<=60;$i++)
{
  imagesetpixel($im,rand(0,60),rand(0,32),$green);
}

//8 把生成的随机数写入画布
// imagestring  水平地画一行字符串
// bool imagestring ( resource $image , int $font , int $x , int $y , string $s , int $col )

$x = 8;
for($j=0;$j<4;$j++)
{
  //写入的数字在Y轴上随机波动
  imagestring($im,5,$x,rand(6,12),substr($code,$j,1),$green);
  $x+=10;  //X轴自增,每次加10,不使写入的数字重叠在一起
}

//9 输出图片然后销毁
// imagepng  以 PNG 格式将图像输出到浏览器或文件
imagepng($im);
imagedestroy($im);
?>