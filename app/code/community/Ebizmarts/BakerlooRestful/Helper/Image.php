<?php

/**
 * Base code taken from https://gist.github.com/philBrown/880506
 *
 */
class Ebizmarts_BakerlooRestful_Helper_Image
{

    /**
     * @var int
     */
    protected $_width;

    /**
     * @var int
     */
    protected $_height;

    /**
     * @var resource
     */
    protected $_image;

    /**
     * Image manipulator constructor
     *
     * @param string $file OPTIONAL Path to image file or image data as string
     */
    public function __construct($file = null)
    {
        if (null !== $file) {
            $fileObj = $this->getFileObject($file);

            if ($fileObj->isFile()) {
                $this->setImageFile($file);
            } else {
                $this->setImageString($file);
            }
        }
    }

    public function getFileObject($path)
    {
        return new SplFileInfo($path);
    }

    /**
     * Set image resource from file
     *
     * @param string $file Path to image file
     * @return ImageManipulator for a fluent interface
     * @throws InvalidArgumentException
     */
    public function setImageFile($file)
    {
        $fileObj = $this->getFileObject($file);

        if (!($fileObj->isReadable() && $fileObj->isFile())) {
            throw new InvalidArgumentException("Image file $file is not readable");
        }

        if (is_resource($this->_image)) {
            imagedestroy($this->_image);
        }

        list ($this->_width, $this->_height, $type) = getimagesize($file);

        switch ($type) {
            case IMAGETYPE_GIF:
                $this->_image = imagecreatefromgif($file);
                break;
            case IMAGETYPE_JPEG:
                $this->_image = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_PNG:
                $this->_image = imagecreatefrompng($file);
                break;
            default:
                throw new InvalidArgumentException("Image type $type not supported");
        }

        $this->_width  = imagesx($this->_image);
        $this->_height = imagesy($this->_image);

        return $this;
    }

    /**
     * Set image resource from string data
     *
     * @param string $data
     * @return ImageManipulator for a fluent interface
     * @throws RuntimeException
     */
    public function setImageString($data)
    {
        if (is_resource($this->_image)) {
            imagedestroy($this->_image);
        }

        try {
            if (!$this->_image = imagecreatefromstring($data)) {
                throw new RuntimeException('Cannot create image from data string');
            }

            $this->_width  = imagesx($this->_image);
            $this->_height = imagesy($this->_image);
            return $this;
        } catch (Exception $e) {
            throw new RuntimeException('Cannot create image from data string: ' . $e->getMessage());
        }
    }

    /**
     * Resamples the current image
     *
     * @param int  $width                New width
     * @param int  $height               New height
     * @param bool $constrainProportions Constrain current image proportions when resizing
     * @return ImageManipulator for a fluent interface
     * @throws RuntimeException
     */
    public function resample($width, $height, $constrainProportions = true)
    {
        if (!is_resource($this->_image)) {
            throw new RuntimeException('No image set');
        }
        if ($constrainProportions) {
            if ($this->_height >= $this->_width) {
                $width  = round($height / $this->_height * $this->_width);
            } else {
                $height = round($width / $this->_width * $this->_height);
            }
        }
        $temp = imagecreatetruecolor($width, $height);
        imagecopyresampled($temp, $this->_image, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);
        return $this->_replace($temp);
    }

    /**
     * Enlarge canvas
     *
     * @param int   $width  Canvas width
     * @param int   $height Canvas height
     * @param array $rgb    RGB colour values
     * @param int   $xpos   X-Position of image in new canvas, null for centre
     * @param int   $ypos   Y-Position of image in new canvas, null for centre
     * @return ImageManipulator for a fluent interface
     * @throws RuntimeException
     */
    public function enlargeCanvas($width, $height, array $rgb = array(), $xpos = null, $ypos = null)
    {
        if (!is_resource($this->_image)) {
            throw new RuntimeException('No image set');
        }

        $width  = max($width, $this->_width);
        $height = max($height, $this->_height);

        $temp = imagecreatetruecolor($width, $height);
        if (count($rgb) == 3) {
            $bg = imagecolorallocate($temp, $rgb[0], $rgb[1], $rgb[2]);
            imagefill($temp, 0, 0, $bg);
        }

        if (null === $xpos) {
            $xpos = round(($width - $this->_width) / 2);
        }
        if (null === $ypos) {
            $ypos = round(($height - $this->_height) / 2);
        }

        imagecopy($temp, $this->_image, (int) $xpos, (int) $ypos, 0, 0, $this->_width, $this->_height);
        return $this->_replace($temp);
    }

    /**
     * Crop image
     *
     * @param int|array $x1 Top left x-coordinate of crop box or array of coordinates
     * @param int       $y1 Top left y-coordinate of crop box
     * @param int       $x2 Bottom right x-coordinate of crop box
     * @param int       $y2 Bottom right y-coordinate of crop box
     * @return ImageManipulator for a fluent interface
     * @throws RuntimeException
     */
    public function crop($x1, $y1 = 0, $x2 = 0, $y2 = 0)
    {
        if (!is_resource($this->_image)) {
            throw new RuntimeException('No image set');
        }
        if (is_array($x1) && 4 == count($x1)) {
            list($x1, $y1, $x2, $y2) = $x1;
        }

        $x1 = max($x1, 0);
        $y1 = max($y1, 0);

        $x2 = min($x2, $this->_width);
        $y2 = min($y2, $this->_height);

        $width  = $x2 - $x1;
        $height = $y2 - $y1;

        $temp = imagecreatetruecolor($width, $height);
        imagecopy($temp, $this->_image, 0, 0, $x1, $y1, $width, $height);

        return $this->_replace($temp);
    }

    public function cropCentered($w, $h)
    {
        if (!is_resource($this->_image)) {
            throw new RuntimeException('No image set');
        }

        $cx = $this->_width / 2;
        $cy = $this->_height / 2;
        $x = $cx - $w / 2;
        $y = $cy - $h / 2;

        $x1 = max($x, 0);
        //$y1 = max($y, 0);

        $temp = imagecreatetruecolor($w, $h);

        //Crops centered x and y
        //imagecopy($temp, $this->image, 0, 0, $x1, $y1, $w, $h);

        //Crops centerax x, y = 0, means, from top
        imagecopy($temp, $this->_image, 0, 0, $x1, 0, $w, $h);

        if ($h > $this->_height) {
            $white = imagecolorallocate($temp, 255, 255, 255);
            imagefill($temp, 0, $this->_height, $white);
        }

        return $this->_replace($temp);
    }

    /**
     * Replace current image resource with a new one
     *
     * @param resource $res New image resource
     * @return ImageManipulator for a fluent interface
     * @throws UnexpectedValueException
     */
    protected function _replace($res)
    {
        if (!is_resource($res)) {
            throw new UnexpectedValueException('Invalid resource');
        }
        if (is_resource($this->_image)) {
            imagedestroy($this->_image);
        }
        $this->_image = $res;
        $this->_width = imagesx($res);
        $this->_height = imagesy($res);
        return $this;
    }

    /**
     * Save current image to file
     *
     * @param string $fileName
     * @param int $type
     * @return bool
     * @throws RuntimeException
     */
    public function save($fileName, $type = IMAGETYPE_JPEG)
    {

        $dirObj = $this->getFileObject($fileName);

        $dir = $this->getFileObject($dirObj->getPath());

        if (!$dir->isDir()) {
            $cacheDirName = $dirObj->getPath();

            unset($dirObj);
            unset($dir);

            $io = new Varien_Io_File;
            if (!$io->mkdir($cacheDirName, 0755, true)) {
                return false;
            }
        }

        if (isset($dirObj)) {
            unset($dirObj);
        }

        if (isset($dir)) {
            unset($dir);
        }

        try {
            switch ($type) {
                case IMAGETYPE_GIF:
                    if (!imagegif($this->_image, $fileName)) {
                        return false;
                    }
                    break;
                case IMAGETYPE_PNG:
                    if (!imagepng($this->_image, $fileName)) {
                        return false;
                    }
                    break;
                case IMAGETYPE_JPEG:
                default:
                    if (!imagejpeg($this->_image, $fileName, 100)) {
                        return false;
                    }
            }
        } catch (Exception $ex) {
            Mage::logException($ex);
            return false;
        }

        return true;
    }

    /**
     * Returns the GD image resource
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->_image;
    }

    /**
     * Get current image resource width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * Get current image height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }
}
