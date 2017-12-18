<?php

/**
 * This is the model class for table "blocks".
 *
 * The followings are the available columns in table 'blocks':
 * @property integer $id
 * @property string $name
 * @property string $platform_id
 * @property string $size
 * @property integer $custom_horizontal_size
 * @property integer $custom_vertical_size
 * @property integer $horizontal_count
 * @property integer $vertical_count
 * @property string $header_align
 * @property string $font_name
 * @property string $font_size
 * @property string $font_color
 * @property string $image_size
 * @property integer $header
 * @property integer $external_border_width
 * @property string $external_border_color
 * @property integer $internal_border_width
 * @property string $internal_border_color
 * @property string $html
 * @property string $css
 * @property integer $use_client_code
 *
 * The followings are the available model relations:
 * @property Platforms $platform
 *
 * @method Blocks findByPk()
 */
class Blocks extends CActiveRecord
{
    public $font_size = '12px';
    public $font_color = '#40454C';
    public $custom_horizontal_size = 200;
    public $custom_vertical_size = 200;
    public $external_border_width = 1;
    public $external_border_color = '#000000';
    public $internal_border_width = 1;
    public $internal_border_color = '#000000';

    public function getTemplates()
    {
        return array(
            '240x400' => array(
                'imageSize' => 90,
                'hCount' => 1,
                'vCount' => 4,
                'vPadding' => 10,
            ),
            '728x60'  => array(
                'imageSize' => 50,
                'hCount' => 4,
                'vCount' => 1,
                'vPadding' => 10,
            ),
            '600x120' => array(
                'imageSize' => 110,
                'hCount' => 2,
                'vCount' => 1,
                'vPadding' => 10,
            ),
            '120x600' => array(
                'imageSize' => 110,
                'hCount' => 1,
                'vCount' => 3,
                'vPadding' => 10,
            ),
            '120x400' => array(
                'imageSize' => 110,
                'hCount' => 1,
                'vCount' => 2,
                'vPadding' => 10,
            ),
            '160x600' => array(
                'imageSize' => 70,
                'hCount' => 1,
                'vCount' => 7,
                'vPadding' => 10,
            ),
            '500x400' => array(
                'imageSize' => 90,
                'hCount' => 2,
                'vCount' => 4,
                'vPadding' => 10,
            ),
            '300x250' => array(
                'imageSize' => 110,
                'hCount' => 1,
                'vCount' => 2,
                'vPadding' => 10
            ),
        );
    }

    public function getAvailableSizes()
    {
        $templates = array_keys($this->getTemplates());
        $templates = array_combine($templates, $templates);
        return array_merge($templates, array(
            'custom'  => 'Задать'
        ));
    }

    public function getAvailableHeaderAligns()
    {
        return array(
            'right' => 'Справа',
            'left' => 'Слева',
            'center' => 'По центру',
        );
    }

    public function getAvailableFontNames()
    {
        return array(
            '' => 'По умолчанию',
            'Arial' => 'Arial',
            'Tahoma' => 'Tahoma',
            'Times New Roman' => 'Times New Roman'
        );
    }

    public function getAvailableFontSizes()
    {
        $sizes = array();
        for($i = 9; $i < 31; $i++){
            $sizes[$i.'px'] = $i;
        }
        return $sizes;
    }

    public function getAvailableImageSizes()
    {
        return array(
            '50x50' => '50x50',
            '60x60' => '60x60',
            '70x70' => '70x70',
            '80x80' => '80x80',
            '90x90' => '90x90',
            '100x100' => '100x100',
            '110x110' => '110x110',
            '120x120' => '120x120',
            '130x130' => '130x130',
            '140x140' => '140x140',
            '150x150' => '150x150',
            '200x200' => '200x200',
        );
    }

//    public function getAvailableBorderTypes()
//    {
//        return array(
//            'solid' => 'Сплошная',
//            'dotted' => 'Точки',
//            'dashed' => 'Пунктир',
//            'double' => 'Двойная',
//        );
//    }

    /**
     * @return Blocks Именованная группа для выборки доступных пользователю площадок
     */
    public function available()
    {
        if(Yii::app()->user->role != Users::ROLE_ADMIN){
            $this->with('platform')->getDbCriteria()->mergeWith(array(
                'condition' => 'platform.user_id = :userId',
                'params' => array(':userId' => Yii::app()->user->id),
                'order' => 'platform_id, name ASC'
            ));
        }else{
            $this->getDbCriteria()->mergeWith(array(
                'order' => 'name ASC'
            ));
        }

        return $this;
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'name' => 'Название',
            'platform_id' => 'Площадка',
            'size' => 'Размер блока',
            'custom_horizontal_size' => 'Custom Horizontal Size',
            'custom_vertical_size' => 'Custom Vertical Size',
            'horizontal_count' => 'Horizontal Count',
            'vertical_count' => 'Vertical Count',
            'header_align' => 'Положение заголовка',
            'font_name' => 'Семейство шрифтов',
            'font_size' => 'Размер шрифта',
            'font_color' => 'Цвет текста',
            'image_size' => 'Размер изображения',
            'header' => 'Отображать партнерский заголовок',
            'external_border_width' => 'Внешняя граница',
            'external_border_color' => 'External Border Color',
            'internal_border_width' => 'Внутренняя граница',
            'internal_border_color' => 'Internal Border Color',
            'html' => 'HTML-шаблон',
            'css' => 'CSS-шаблон',
            'use_client_code' => 'Использовать продвинутый механизм защиты от блокировки рекламы'
        );
    }

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Blocks the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'blocks';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, platform_id, header, css', 'required'),
			array('custom_horizontal_size, custom_vertical_size, horizontal_count, vertical_count, header, external_border_width, internal_border_width, use_client_code', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
			array('platform_id', 'length', 'max'=>10),
			array('size, header_align, font_name, font_size, font_color, image_size, external_border_color, internal_border_color', 'length', 'max'=>45),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, platform_id, size, custom_horizontal_size, custom_vertical_size, horizontal_count, vertical_count, header_align, font_name, font_size, font_color, image_size, header, external_border_width, external_border_color, internal_border_width, internal_border_color, html', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'platform' => array(self::BELONGS_TO, 'Platforms', 'platform_id'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('platform_id',$this->platform_id,true);
		$criteria->compare('size',$this->size,true);
		$criteria->compare('custom_horizontal_size',$this->custom_horizontal_size);
		$criteria->compare('custom_vertical_size',$this->custom_vertical_size);
		$criteria->compare('horizontal_count',$this->horizontal_count);
		$criteria->compare('vertical_count',$this->vertical_count);
		$criteria->compare('header_align',$this->header_align,true);
		$criteria->compare('font_name',$this->font_name,true);
		$criteria->compare('font_size',$this->font_size,true);
		$criteria->compare('font_color',$this->font_color,true);
		$criteria->compare('image_size',$this->image_size,true);
		$criteria->compare('header',$this->header);
		$criteria->compare('external_border_width',$this->external_border_width);
		$criteria->compare('external_border_color',$this->external_border_color,true);
		$criteria->compare('internal_border_width',$this->internal_border_width);
		$criteria->compare('internal_border_color',$this->internal_border_color,true);
		$criteria->compare('html',$this->html,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    private function getCssFileName()
    {
        return Yii::getPathOfAlias('client_css') . DIRECTORY_SEPARATOR .$this->id .'.css';
    }

    protected function afterSave()
    {
        file_put_contents($this->getCssFileName() , $this->css);
        return parent::afterSave();
    }

    protected function afterDelete()
    {
        @unlink($this->getCssFileName());
        return parent::afterDelete();
    }
}