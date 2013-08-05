<? include 'helper.php'; ?>

<!doctype html>
<html lang="en">
    <head>
            <title>sam-choi</title>
            <meta charset="utf-8">
            <link href="css/style.css" rel="stylesheet" type="text/css">
            <script type="text/javascript">
                if (window.WebGLRenderingContext) {
                    document.location = '/three.php';   
                }
            </script>
            <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
            <script src="js/main.js"></script>
            <script src='spectrum/spectrum.js'></script>
            <link rel='stylesheet' href='spectrum/spectrum.css' />
    </head>
    <body>
<audio id="music" src="/music/<?= song() ?>" preload="auto"></audio>	

<p class='aero'>
Ever since my blog died, sam-choi.com is now a hack-pad...
</p>
<p id="trip" class='aero'></p>
<table>
<? 
$x_max=16; 
$y_max=64; 
$ids = array();
for($x=0; $x<$x_max; $x++) {
	  for($y=0; $y<$y_max; $y++) {
	  	    $ids[] = $x.'_'.$y;
	  }
}
shuffle($ids);
$sum=0;
for($x=0; $x<$x_max; $x++) { 
?>
<tr>
<? for($y=0; $y<$y_max; $y++) { ?>
<td id='<?= $ids[$sum++] ?>'></td>
<? } ?>
</tr>
<? } ?>
</table> 

<input id='color' type='text' />

<div id='file_info'>
  <p><?= $song ?></p>
</div> 	



	</body>
</html>
