# Yii2 image cutter

This is a fork [mtrim//yii2-image-cutter](https://github.com/mitrm/yii2-image-cutter-size)

#### Features:
- Bootstrap5
- Upload image
- Crop image
- Use Imagine

![cutter](https://cloud.githubusercontent.com/assets/9282021/8411519/fd601b0e-1e8c-11e5-83a5-1f8c4195f562.jpg)

### Composer

The preferred way to install this extension is through [Composer](http://getcomposer.org/).

Either run ```php composer.phar require --prefer-dist calcio/yii2-image-cutter "dev-master"```

or add ```"calcio/yii2-image-cutter": "dev-master"``` to the require section of your ```composer.json```

### Use

* Add to the model behavior

```php
    use calcio\cutter\behaviors\CutterBehavior;

    public function behaviors()
    {
        return [
            'image' => [
                'class' => CutterBehavior::className(),
                'attributes' => 'image',
                'baseDir' => '/uploads/crop',
                'basePath' => '@webroot',
            ],
        ]
    }
    
    public function rules()
    {
        return [
            ['image', 'file', 'extensions' => 'jpg, jpeg, png', 'mimeTypes' => 'image/jpeg, image/png'],
        ];
    }
```

#### Parameters
- integer `attributes` required (string) - Image attributes
- integer `baseDir` required - Base directory
- integer `basePath` required - Base path
- integer `quality` =  `92` - Crop result quality

* Use in view
> Without client validation

```php
    <div class="form-group">
        <label class="control-label">Image</label>
        <?= \calcio\cutter\Cutter::widget([
            'model' => $model,
            'attribute' => 'image'
        ]); ?>
    </div>
```

or

> With client validation

```php
    <?= $form->field($model, 'image')->widget(\calcio\cutter\Cutter::className(), [
        //options
    ]); ?>
```

* Use in view

> Get url to image

```php
    $model->getImg(100);
    $model-> getImgOrigin();
```
## Widget method options

* model (string) (obligatory)
> Defines the model that will be used to make the form input field.

* attribute (string) (obligatory)
> Defines the model attribute that will be used to make de form input field.

* useWindowHeight (bool) (optional)
> Use the height of the current window for the form image cropping

* imageOptions (array) (optional)
> List with options that will be added to the image field that will be used to define the crop data in the modal. The format should be ['option' => 'value'].

* cropperOptions (array) (optional)
> List with options that will be added in javaScript while creating the crop object. For more information about which options can be added you can [read this web](https://github.com/fengyuanchen/cropper#options).
