<?php
include_once('index.php');
include_once('classes.php');
$mp3Inst = new Mp3();

$message = '';
if (isset($_POST['submit']) && $_POST['submit'] == 'Submit') {
    if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] === UPLOAD_ERR_OK) {
        
        // get details of the uploaded file
        $fileTmpPath = $_FILES['fileToUpload']['tmp_name'];
        $fileName = $_FILES['fileToUpload']['name'];
        $fileSize = $_FILES['fileToUpload']['size'];
        $fileType = $_FILES['fileToUpload']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // sanitize file-name
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        // check if file has one of the following extensions
        $allowedfileExtensions = array('mp3', 'wav');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            // directory in which the uploaded file will be moved
            $uploadFileDir = 'upload/';
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $message = 'File is successfully uploaded.';
                $fileHandle = fopen($dest_path, 'rb');
                $binary = fread($fileHandle, 5);

                // Detect presence of ID3 information.
                if (substr($binary, 0, 3) == "ID3") {
                    // ID3 tags detected.
                    $tags['FileName'] = ;
                    $tags['TAG'] = substr($binary, 0, 3);
                    $tags['Version'] = hexdec(bin2hex(substr($binary, 3, 1))) . "." . hexdec(bin2hex(substr($binary, 4, 1)));
                }
                function headerOffset($fileHandle)
                {
                    // Extract the first 10 bytes of the file and set the handle back to 0.
                    fseek($fileHandle, 0);
                    $block = fread($fileHandle, 10);
                    fseek($fileHandle, 0);
                    $offset = 0;

                    if (substr($block, 0, 3) == "ID3") {
                        // We can ignore bytes 3 and 4 so they aren't extracted here.
                        // Extract ID3 flags.
                        $id3v2Flags = ord($block[5]);
                        $flagUnsynchronisation  = $id3v2Flags & 0x80 ? 1 : 0;
                        $flagExtendedHeader    = $id3v2Flags & 0x40 ? 1 : 0;
                        $flagExperimental   = $id3v2Flags & 0x20 ? 1 : 0;
                        $flagFooterPresent     = $id3v2Flags & 0x10 ? 1 : 0;

                        // Extract the length bytes.
                        $length0 = ord($block[6]);
                        $length1 = ord($block[7]);
                        $length2 = ord($block[8]);
                        $length3 = ord($block[9]);

                        // Check to make sure this is a safesynch integer by looking at the starting bit.
                        if ((($length0 & 0x80) == 0) && (($length1 & 0x80) == 0) && (($length2 & 0x80) == 0) && (($length3 & 0x80) == 0)) {
                            // Extract the tag size.
                            $tagSize = $length0 << 21 | $length1 << 14 | $length2 << 7 | $length3;
                            // Find out the length of other elements based on header size and footer flag.
                            $headerSize = 10;
                            $footerSize = $flagFooterPresent ? 10 : 0;
                            // Add this all together.
                            $offset = $headerSize + $tagSize + $footerSize;
                        }
                    }
                    return $offset;
                }
                $id3v22 = ["TT2", "TAL", "TP1", "TRK", "TYE", "TLE", "ULT"];
                for ($i = 0; $i < count($id3v22); $i++) {
                    // Look for each tag within the data of the file.
                    if (strpos($binary, $id3v22[$i] . chr(0)) != FALSE) {

                        // Extract the tag position and length of data.
                        $pos = strpos($binary, $id3v22[$i] . chr(0));
                        $len = hexdec(bin2hex(substr($binary, ($pos + 3), 3)));
                        $data = substr($binary, ($pos + 6), $len);
                        $tag = substr($binary, $pos, 3);

                        // Extract data.
                        $tagData = '';
                        for ($a = 0; $a <= strlen($data); $a++) {
                            $char = substr($data, $a, 1);
                            if (ord($char) != 0 && ord($char) != 3 && ord($char) != 225 && ctype_print($char)) {
                                $tagData .= $char;
                            } elseif (ord($char) == 225 || ord($char) == 13) {
                                $tagData .= "\n";
                            }
                        }

                        if ($tag == "TT2") {
                            $tags['Title'] = $tagData;
                        }

                        if ($tag == "TAL") {
                            $tags['Album'] = $tagData;
                        }

                        if ($tag == "TP1") {
                            $tags['Author'] = $tagData;
                        }

                        if ($tag == "TRK") {
                            $tags['Track'] = $tagData;
                        }

                        if ($tag == "TYE") {
                            $tags['Year'] = $tagData;
                        }

                        if ($tag == "TLE") {
                            $tags['Length'] = $tagData;
                        }

                        if ($tag == "ULT") {
                            $tags['Lyric'] = $tagData;
                        }
                    }
                }
                $id3v23 = ["TIT2", "TALB", "TPE1", "TRCK", "TYER", "TLEN", "USLT"];
                // Look for each tag within the data of the file.
                for ($i = 0; $i < count($id3v23); $i++) {
                    if (strpos($binary, $id3v23[$i] . chr(0)) != FALSE) {

                        // Extract the tag position and length of data.
                        $pos = strpos($binary, $id3v23[$i] . chr(0));
                        $len = hexdec(bin2hex(substr($binary, ($pos + 5), 3)));
                        $data = substr($binary, ($pos + 10), $len);
                        $tag = substr($binary, $pos, 4);

                        // Extract tag and data.
                        $tagData = '';
                        for ($a = 0; $a <= strlen($data); $a++) {
                            $char = substr($data, $a, 1);
                            if (ord($char) != 0 && ord($char) != 3 && ord($char) != 225 && ctype_print($char)) {
                                $tagData .= $char;
                            } elseif (ord($char) == 225 || ord($char) == 13) {
                                $tagData .= "\n";
                            }
                        }

                        if ($tag == "TIT2") {
                            $tags['Title'] = $tagData;
                        }

                        // the rest of the tags would be extracted here into the tags array.

                    }


                    echo $mp3Inst->readAudioData($fileHandle);
                }
            } else {
                $message = 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
            }
        } else {
            $message = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
        }
    } else {
        $message = 'There is some error in the file upload. Please check the following error.<br>';
        $message .= 'Error:' . $_FILES['uploadedFile']['error'];
    }
}
$_SESSION['message'] = $message;
echo $message;
