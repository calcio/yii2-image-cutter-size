<?php

namespace calcio\cutter;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\bootstrap5\Modal;
use yii\bootstrap5\ButtonGroup;

/**
 * Class Cutter
 * @package calcio\cutter\widgets
 */
class Cutter extends \yii\widgets\InputWidget
{
    /**
     * Image options
     * @var
     */
    public $imageOptions;

    /**
     * Use the height of the current window for the form image cropping
     * @var bool
     */
    public $useWindowHeight = true;

    /**
     * Cropper options
     * @var array
     */
    public $cropperOptions = [];

    /**
     * Default cropper options
     * @var array
     */
    public $defaultCropperOptions = [
        'rotatable' => true,
        'zoomable' => true,
        'movable' => true,
    ];

    public function init()
    {
        parent::init();

        $this->registerTranslations();

        $this->cropperOptions = array_merge($this->cropperOptions, $this->defaultCropperOptions);
    }

    public function run()
    {
        if (is_null($this->imageOptions)) {
            $this->imageOptions = [
                'class' => 'img-fluid',
            ];
        }

        $this->imageOptions['id'] = Yii::$app->getSecurity()->generateRandomString(10);

        $inputField = Html::getInputId($this->model, $this->attribute);

        echo Html::beginTag('div', ['id' => $inputField . '-cutter']);
        echo Html::activeFileInput($this->model, $this->attribute);

        echo Html::beginTag('div', [
            'class' => 'preview-pane',
            'style' => $this->model->{$this->attribute} ? 'display:block' : 'display:none'
        ]);

        echo Html::beginTag('div', ['class' => 'preview-container']);
        echo Html::img($this->model->{$this->attribute} ? $this->model->getImg(500, $this->attribute) : null, [
            'class' => 'preview-image img-fluid',
        ]);
        echo Html::endTag('div');
        echo Html::endTag('div');

        echo Html::checkbox($this->attribute . '-remove', false, [
            'label' => Yii::t('calcio/cutter/cutter', 'REMOVE')
        ]);

        Modal::begin([
            'closeButton' => [],
            'footer' => $this->getModalFooter($inputField),
            'size' => Modal::SIZE_LARGE,
        ]);

        echo Html::beginTag('div', ['class' => 'image-container']);
        echo Html::img(null, $this->imageOptions);
        echo Html::endTag('div');

        echo Html::tag('br');

        echo Html::beginTag('div', ['class' => 'row']);
        echo Html::beginTag('div', ['class' => 'col-md-2']);
        echo Html::label(Yii::t('calcio/cutter/cutter', 'ASPECT_RATIO'), $inputField . '-aspectRatio');
        echo Html::textInput($this->attribute . '-aspectRatio', isset($this->cropperOptions['aspectRatio']) ? $this->cropperOptions['aspectRatio'] : 0, ['id' => $inputField . '-aspectRatio', 'class' => 'form-control']);
        echo Html::endTag('div');

        echo Html::beginTag('div', ['class' => 'col-md-2']);
        echo Html::label(Yii::t('calcio/cutter/cutter', 'ANGLE'), $inputField . '-dataRotate');
        echo Html::textInput($this->attribute . '-cropping[dataRotate]', '', ['id' => $inputField . '-dataRotate', 'class' => 'form-control']);
        echo Html::endTag('div');

        echo Html::beginTag('div', ['class' => 'col-md-2']);
        echo Html::label(Yii::t('calcio/cutter/cutter', 'POSITION') . ' (X)', $inputField . '-dataX');
        echo Html::textInput($this->attribute . '-cropping[dataX]', '', ['id' => $inputField . '-dataX', 'class' => 'form-control']);
        echo Html::endTag('div');

        echo Html::beginTag('div', ['class' => 'col-md-2']);
        echo Html::label(Yii::t('calcio/cutter/cutter', 'POSITION') . ' (Y)', $inputField . '-dataY');
        echo Html::textInput($this->attribute . '-cropping[dataY]', '', ['id' => $inputField . '-dataY', 'class' => 'form-control']);
        echo Html::endTag('div');

        echo Html::beginTag('div', ['class' => 'col-md-2']);
        echo Html::label(Yii::t('calcio/cutter/cutter', 'WIDTH'), $inputField . '-dataWidth');
        echo Html::textInput($this->attribute . '-cropping[dataWidth]', '', ['id' => $inputField . '-dataWidth', 'class' => 'form-control']);
        echo Html::endTag('div');

        echo Html::beginTag('div', ['class' => 'col-md-2']);
        echo Html::label(Yii::t('calcio/cutter/cutter', 'HEIGHT'), $inputField . '-dataHeight');
        echo Html::textInput($this->attribute . '-cropping[dataHeight]', '', ['id' => $inputField . '-dataHeight', 'class' => 'form-control']);
        echo Html::endTag('div');
        echo Html::endTag('div');

        Modal::end();

        echo Html::endTag('div');

        $view = $this->getView();

        CutterAsset::register($view);

        $options = [
            'inputField' => $inputField,
            'useWindowHeight' => $this->useWindowHeight,
            'cropperOptions' => $this->cropperOptions
        ];

        $options = Json::encode($options);

        $view->registerJs('jQuery("#' . $inputField . '").cutter(' . $options . ');');
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['calcio/cutter/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/calcio/yii2-image-cutter/messages',
            'fileMap' => [
                'calcio/cutter/cutter' => 'cutter.php',
            ],
        ];
    }

    private function getModalFooter($inputField)
    {
        return Html::beginTag('div', [
            'class' => 'btn-toolbar pull-left'
        ]) .
            ButtonGroup::widget([
                'encodeLabels' => false,
                'buttons' => [
                    [
                        'label' => '<i class="fa fa-arrows-alt"></i>',
                        'options' => [
                            'type' => 'button',
                            'data-method' => 'setDragMode',
                            'data-option' => 'move',
                            'class' => 'btn btn-primary',
                            'title' => Yii::t('calcio/cutter/cutter', 'DRAG_MODE_MOVE'),
                        ]
                    ],
                    [
                        'label' => '<i class="fa fa-scissors"></i>',
                        'options' => [
                            'type' => 'button',
                            'data-method' => 'setDragMode',
                            'data-option' => 'crop',
                            'class' => 'btn btn-primary',
                            'data-title' => Yii::t('calcio/cutter/cutter', 'DRAG_MODE_CROP'),
                        ]
                    ],
                ],
                'options' => [
                    'class' => 'pull-left'
                ]
            ]) .
            ButtonGroup::widget([
                'encodeLabels' => false,
                'buttons' => [
                    [
                        'label' => '<i class="fa fa-check"></i>',
                        'options' => [
                            'type' => 'button',
                            'data-method' => 'crop',
                            'class' => 'btn btn-primary',
                            'data-title' => Yii::t('calcio/cutter/cutter', 'CROP'),
                        ]
                    ],
                    [
                        'label' => '<i class="fa fa-refresh"></i>',
                        'options' => [
                            'type' => 'button',
                            'data-method' => 'reset',
                            'class' => 'btn btn-primary',
                            'title' => Yii::t('calcio/cutter/cutter', 'REFRESH'),
                        ]
                    ],
                    [
                        'label' => '<i class="fa fa-remove"></i>',
                        'options' => [
                            'type' => 'button',
                            'data-method' => 'clear',
                            'class' => 'btn btn-primary',
                            'title' => Yii::t('calcio/cutter/cutter', 'REMOVE'),
                        ]
                    ],
                ],
                'options' => [
                    'class' => 'pull-left'
                ]
            ]) .
            ButtonGroup::widget([
                'encodeLabels' => false,
                'buttons' => [
                    [
                        'label' => '<i class="fa fa-search-plus"></i>',
                        'options' => [
                            'type' => 'button',
                            'data-method' => 'zoom',
                            'data-option' => '0.1',
                            'class' => 'btn btn-primary',
                            'title' => Yii::t('calcio/cutter/cutter', 'ZOOM_IN'),
                        ],
                        'visible' => $this->cropperOptions['zoomable']
                    ],
                    [
                        'label' => '<i class="fa fa-search-minus"></i>',
                        'options' => [
                            'type' => 'button',
                            'data-method' => 'zoom',
                            'data-option' => '-0.1',
                            'class' => 'btn btn-primary',
                            'title' => Yii::t('calcio/cutter/cutter', 'ZOOM_OUT'),
                        ],
                        'visible' => $this->cropperOptions['zoomable']
                    ],
                    [
                        'label' => '<i class="fa fa-share-alt  icon-flipped"></i>',
                        'options' => [
                            'type' => 'button',
                            'data-method' => 'rotate',
                            'data-option' => '45',
                            'class' => 'btn btn-primary',
                            'title' => Yii::t('calcio/cutter/cutter', 'ROTATE_LEFT'),
                        ],
                        'visible' => $this->cropperOptions['rotatable']
                    ],
                    [
                        'label' => '<i class="fa fa-share-alt"></i>',
                        'options' => [
                            'type' => 'button',
                            'data-method' => 'rotate',
                            'data-option' => '-45',
                            'class' => 'btn btn-primary',
                            'title' => Yii::t('calcio/cutter/cutter', 'ROTATE_RIGHT'),
                        ],
                        'visible' => $this->cropperOptions['rotatable']
                    ],
                ],
                'options' => [
                    'class' => 'pull-left'
                ]
            ]) .
            ButtonGroup::widget([
                'encodeLabels' => false,
                'buttons' => [
                    [
                        'label' => '<i class="fa fa-expand"></i>',
                        'options' => [
                            'type' => 'button',
                            'data-method' => 'setAspectRatio',
                            'data-target' => '#' . $inputField . '-aspectRatio',
                            'class' => 'btn btn-primary',
                            'title' => Yii::t('calcio/cutter/cutter', 'SET_ASPECT_RATIO'),
                        ]
                    ],
                    [
                        'label' => '<i class="fa fa-upload"></i>',
                        'options' => [
                            'type' => 'button',
                            'data-method' => 'setData',
                            'class' => 'btn btn-primary',
                            'title' => Yii::t('calcio/cutter/cutter', 'SET_DATA'),
                        ]
                    ],
                ],
                'options' => [
                    'class' => 'pull-left'
                ]
            ]) .
            Html::endTag('div') .
            Html::button(Yii::t('calcio/cutter/cutter', 'CANCEL'), [
                'id' => $this->imageOptions['id'] . '_button_cancel', 'class' => 'btn btn-danger'
            ]) . Html::button(Yii::t('calcio/cutter/cutter', 'ACCEPT'), [
                'id' => $this->imageOptions['id'] . '_button_accept', 'class' => 'btn btn-success'
            ]);
    }
}
