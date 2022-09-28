<?php
class Mp3
{

  protected $versions = [
    0x0 => '2.5',
    0x1 => 'x',
    0x2 => '2',
    0x3 => '1',
  ];

  protected $layers = [
    0x0 => 'x',
    0x1 => '3',
    0x2 => '2',
    0x3 => '1',
  ];

  protected $bitrates = [
    'V1L1' => [0, 32, 64, 96, 128, 160, 192, 224, 256, 288, 320, 352, 384, 416, 448],
    'V1L2' => [0, 32, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320, 384],
    'V1L3' => [0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320],
    'V2L1' => [0, 32, 48, 56, 64, 80, 96, 112, 128, 144, 160, 176, 192, 224, 256],
    'V2L2' => [0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160],
    'V2L3' => [0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160],
  ];

  protected $samplerates = [
    '1'   => [44100, 48000, 32000],
    '2'   => [22050, 24000, 16000],
    '2.5' => [11025, 12000, 8000],
  ];

  protected $samples = [
    1 => [1 => 384, 2 => 1152, 3 => 1152,],
    2 => [1 => 384, 2 => 1152, 3 => 576,],
  ];

  // Other methods.
  public function readAudioData()
  {
    // // Open the file.
    // $file = fopen($this->file, "rb");

    // Skip header.
    $offset = $this->headerOffset($file);
    fseek($file, $offset, SEEK_SET);

    while (!feof($file)) {
      // We nibble away at the file, 10 bytes at a time.
      $block = fread($file, 8);
      if (strlen($block) < 8) {
        break;
      }
      //looking for 1111 1111 111 (frame synchronization bits)
      else if ($block[0] == "\xff" && (ord($block[1]) & 0xe0)) {
        $fourbytes = substr($block, 0, 4);
        // The first block of bytes will always be 0xff in the framesync
        // so we ignore $fourbytes[0] but need to process $fourbytes[1] for
        // the version information.
        $b1 = ord($fourbytes[1]);
        $b2 = ord($fourbytes[2]);
        $b3 = ord($fourbytes[3]);

        // Extract the version and create a simple version for lookup.
        $version = $this->versions[($b1 & 0x18) >> 3];
        $simpleVersion = ($version == '2.5' ? 2 : $version);

        // Extract layer.
        $layer = $this->layers[($b1 & 0x06) >> 1];

        // Extract protection bit.
        $protectionBit = ($b1 & 0x01);

        // Extract bitrate.
        $bitrateKey = sprintf('V%dL%d', $simpleVersion, $layer);
        $bitrateId = ($b2 & 0xf0) >> 4;
        $bitrate = isset($this->bitrates[$bitrateKey][$bitrateId]) ? $this->bitrates[$bitrateKey][$bitrateId] : 0;

        // Extract the sample rate.
        $sampleRateId = ($b2 & 0x0c) >> 2;
        $sampleRate = isset($this->samplerates[$version][$sampleRateId]) ? $this->samplerates[$version][$sampleRateId] : 0;

        // Extract padding bit.
        $paddingBit = ($b2 & 0x02) >> 1;

        // Extract framesize.
        if ($layer == 1) {
          $framesize = intval(((12 * $bitrate * 1000 / $sampleRate) + $paddingBit) * 4);
        } else {
          // Later 2 and 3.
          $framesize = intval(((144 * $bitrate * 1000) / $sampleRate) + $paddingBit);
        }

        // Extract samples.
        $frameSamples = $this->samples[$simpleVersion][$layer];

        // Extract other bits.
        $channelModeBits = ($b3 & 0xc0) >> 6;
        $modeExtensionBits = ($b3 & 0x30) >> 4;
        $copyrightBit = ($b3 & 0x08) >> 3;
        $originalBit = ($b3 & 0x04) >> 2;
        $emphasis = ($b3 & 0x03);

        // Calculate the duration and add this to the running total.
        $this->duration += ($frameSamples / $sampleRate);

        // Read the frame data into memory.
        $frameData = fread($file, $framesize - 8);
  
        // do something with the frame data.
      } else if (substr($block, 0, 3) == 'TAG') {
        // If this is a tag then jump over it.
        fseek($file, 128 - 10, SEEK_CUR);
      } else {
        fseek($file, -9, SEEK_CUR);
      }
    }
    return $this->$sampleRate;
  }
}
?>