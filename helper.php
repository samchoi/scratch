<?php
function song(){
    $music = scandir('music');
    unset($music[0]); unset($music[1]);
    $music = array_values($music);
    $song = isset($_GET['s']) ? $_GET['s'] : $music[rand(0, sizeof($music)-1)];
    return $song;    
}
?>
