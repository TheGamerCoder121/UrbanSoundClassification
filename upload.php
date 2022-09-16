<?php
$target_file = basename($_FILES["fileToUpload"]["name"]);
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
$tags

$fileHandle = fopen($target_file . $imageFileType, 'rb');
$binary = fread($fileHandle, 5);

// Detect presence of ID3 information.
if (substr($binary, 0, 3) == "ID3") {
  // ID3 tags detected.
  $tags['FileName'] = $file;
  $tags['TAG'] = substr($binary, 0, 3);
  $tags['Version'] = hexdec(bin2hex(substr($binary, 3, 1))) . "." . hexdec(bin2hex(substr($binary, 4, 1)));
}

echo tags

?>
