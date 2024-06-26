<?php

namespace calcio\cutter\behaviors;

use Yii;
use yii\helpers\Json;
use yii\image\ImageDriver;
use yii\imagine\Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Palette\Color\RGB as RGBColor;

/**
 * Class CutterBehavior
 * @package mitrm\cutter\behavior
 */
class CutterBehavior extends \yii\behaviors\AttributeBehavior
{
    public $expansion = '.png';

    /**
     * Attributes
     * @var
     */
    public $attributes;

    /**
     * Base directory
     * @var
     */
    public $baseDir;

    /**
     * Base path
     * @var
     */
    public $basePath;

    /**
     * Image cut quality
     * @var int
     */
    public $quality = 100;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeUpload',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpload',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    public function beforeUpload()
    {
        if (is_array($this->attributes) && count($this->attributes)) {
            foreach ($this->attributes as $attribute) {
                $this->upload($attribute);
            }
        } else {

            $this->upload($this->attributes);
        }
    }

    public function upload($attribute)
    {
        if ($uploadImage = UploadedFile::getInstance($this->owner, $attribute)) {
            if (!$this->owner->isNewRecord) {
                $this->delete($attribute);
            }
            $cropping = $_POST[$attribute . '-cropping'];

            $croppingFileName = md5($uploadImage->name . $this->quality . Json::encode($cropping));
            $croppingFileExt = $this->expansion;

            $croppingFileBasePath = Yii::getAlias($this->basePath);

            if (!is_dir($croppingFileBasePath)) {
                mkdir($croppingFileBasePath, 0755, true);
            }
            $croppingFilePath = Yii::getAlias($this->basePath);

            if (!is_dir($croppingFilePath)) {
                mkdir($croppingFilePath, 0755, true);
            }

            $croppingFile = $croppingFilePath . DIRECTORY_SEPARATOR . $croppingFileName . $croppingFileExt;
            if (!empty($uploadImage->tempName)) {
                $imageTmp = Image::getImagine()->open($uploadImage->tempName);
                $imageTmp->rotate($cropping['dataRotate']);

                $palette = new RGB();
                $color = $palette->color('fff', 0);

                $image = Image::getImagine()->create($imageTmp->getSize(), $color);
                $image->paste($imageTmp, new Point(0, 0));

                $point = new Point($cropping['dataX'], $cropping['dataY']);
                $box = new Box($cropping['dataWidth'], $cropping['dataHeight']);

                $image->crop($point, $box);
                $image->save($croppingFile, ['quality' => $this->quality]);

                $this->owner->{$attribute} = $croppingFileName;
            }
        } elseif (isset($_POST[$attribute . '-remove']) && $_POST[$attribute . '-remove']) {
            $this->delete($attribute);
        } elseif (isset($this->owner->oldAttributes[$attribute])) {
            $this->owner->{$attribute} = $this->owner->oldAttributes[$attribute];
        }
    }

    public function beforeDelete()
    {
        if (is_array($this->attributes) && count($this->attributes)) {
            foreach ($this->attributes as $attribute) {
                $this->delete($attribute);
            }
        } else {
            $this->delete($this->attributes);
        }
    }

    public function delete($attribute)
    {
        $name_image = $this->owner->oldAttributes[$attribute];
        if (!empty($name_image)) {
            $mack = Yii::getAlias($this->basePath) . DIRECTORY_SEPARATOR . $name_image . '*';
            @array_map("unlink", glob($mack));
        }
    }

    /**
     * @brief Returns the original of the uploaded image
     * @return string
     */
    public function getImgOrigin($attribute = false)
    {
        if (!is_array($this->attributes)) {
            $attribute = $this->attributes;
        }
        return $this->baseDir . '/' . $this->owner->$attribute . $this->expansion;
    }

    /**
     * @brief Gives the image the right size 
     * @detailed Look self::getImg()
     * @param int $size
     * @return bool|string
     */
    public function getImg($size = 500, $attribute = false)
    {
        if (!is_array($this->attributes)) {
            $attribute = $this->attributes;
        }
        return self::getImgUrl($this->owner->$attribute, $size);
    }

    /**
     * @brief Gives the image the right size 
     * @detailed if the image does not fit the desired size, it generates it in real time 
     * @param $img
     * @param int $size
     * @return bool|string
     */
    public function getImgUrl($img, $size = 500)
    {
        $image = $this->baseDir . '/' . $img . '_' . $size . 'x' . $size . $this->expansion;
        $image_path = $this->basePath . '/' . $img . '_' . $size . 'x' . $size . $this->expansion;
        if (file_exists($image_path)) {
            return $image;
        } else {
            $file = $this->basePath . '/' . $img . $this->expansion;
            if (!file_exists($file)) {
                return false;
            }
            $image = new ImageDriver(['driver' => 'GD']);
            $image = $image->load($file);
            $image->resize($size, $size);
            $image->save($this->basePath . '/' . $img . '_' . $size . 'x' . $size . $this->expansion, 100);
            return $this->baseDir . '/' . $img . '_' . $size . 'x' . $size . $this->expansion;
        }
    }

    /**
     * @brief Gives the image the right size 
     * @detailed if the image does not fit the desired size, it generates it in real time 
     * @param $basePath
     * @param $baseDir
     * @param $img
     * @param int $size
     * @param $expansion
     * @return bool|string
     */
    public static function getImageUrl($basePath, $baseDir, $img, $size = 500, $expansion = '.png')
    {
        $image = $baseDir . '/' . $img . '_' . $size . 'x' . $size . $expansion;
        $image_path = Yii::getAlias($basePath) . '/' . $img . '_' . $size . 'x' . $size . $expansion;
        if (file_exists($image_path)) {
            return $image;
        } else {
            $file = $basePath . '/' . $img . $expansion;
            if (!file_exists($file)) {
                return false;
            }
            $image = new ImageDriver(['driver' => 'GD']);
            $image = $image->load($file);
            $image->resize($size, $size);
            $image->save($basePath . '/' . $img . '_' . $size . 'x' . $size . $expansion, 100);
            return $baseDir . '/' . $img . '_' . $size . 'x' . $size . $expansion;
        }
    }
}
