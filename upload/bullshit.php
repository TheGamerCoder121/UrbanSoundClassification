<?php
include('../classes.php');

$mp31 = new Mp3('2dfd51a0e7be043e76ba157274a613d2.mp3');
$mp31->readAudioData();

echo $mp31;