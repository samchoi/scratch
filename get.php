<?
header('Access-Control-Allow-Origin: *');
$q=array();
$u = $_GET['u'];
$f = $_GET['f'];
$c = file_get_contents($u);
echo file_put_contents("music/".$f, $c);

//$exec = exec($cmd, $q);
//print_r($q);
?>