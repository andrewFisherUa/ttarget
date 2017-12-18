<?php

/**
 * This is the model class for table "offers_images".
 *
 * The followings are the available columns in table 'offers_images':
 * @property string $id
 * @property string $offer_id
 * @property string $filename
 *
 * The followings are the available model relations:
 * @property Offers $offer
 */
class OffersImages extends CActiveRecord
{
	public $file;
	public $thumbWidth;
	public $thumbHeight;
	
	/**
	*
	**/
	public function getUrl()
	{
		return Yii::app()->params->offerImageBaseUrl . DIRECTORY_SEPARATOR . $this->filename;
	}
	
	/**
	*	Delete offer images by it's id
	*	@param offer_id
	*	@param image_ids
	**/
	public function deleteByIds($offer_id, $image_ids = array())
	{
		$_criteria = new CDbCriteria();
		$_criteria -> condition = 'offer_id = :offer_id';
		$_criteria -> params = array(':offer_id' => $offer_id);
		$_criteria -> addInCondition('id', $image_ids);
		$this->deleteAll($_criteria);
	}
	
	
	/**
	*
	**/
	protected function afterFind()
	{
		$targetWidth = $targetHeight = min(Yii::app()->params->offerImageThumbMaxWidth, Yii::app()->params->offerImageThumbMaxHeight, max($this->width, $this->height));
		$ratio = $this->width / $this->height;
		
		if ($ratio < 1) {
		    $targetWidth = $targetHeight * $ratio;
		} else {
		    $targetHeight = $targetWidth / $ratio;
		}
		
		$this->thumbWidth = $targetWidth;
		$this->thumbHeight = $targetHeight;
	}
	
	/**
	*
	**/
	protected function beforeSave()
	{
		if( parent::beforeSave() ){
			
			//Store file
			if(!empty($this->file)){
				try {
	                /** @var Image $img */
	                $img = Yii::app()->image->load(Yii::app()->params->docTmpPath . DIRECTORY_SEPARATOR . $this->file);
	                //$outputFileName = CFile::createUniqueFileName(Yii::app()->params->imageBasePath, '.'.$img->image['ext'], 't_');
	                $img
	                	// ->resize($_crop_x, $_crop_x, Image::NONE)
	                    //->crop(
	                    //    Yii::app()->params->teaserImageWidth,
	                    //    Yii::app()->params->teaserImageHeight,
	                    //    (int)$_REQUEST['crop']['y'],
	                    //    (int)$_REQUEST['crop']['x']
	                    //)
	                    ->save(Yii::app()->params->imageBasePath . DIRECTORY_SEPARATOR . $this->file);
	                
	                	$this->filename = $this->file;
	                	$this->mime     = $img->image['mime'];
	                	$this->width    = $img->image['width'];
	                	$this->height   = $img->image['height'];
	                
	                	unlink(Yii::app()->params->docTmpPath . DIRECTORY_SEPARATOR . $this->file);
	                	$this->file = null;
	                
	            } catch (CException $e){
	            	return false;
	            }
			}
		}
		return true;
	}
	
	/**
	*
	**/
	protected function beforeDelete()
	{
		if(parent::beforeDelete()){
			unlink(Yii::app()->params->imageBasePath . DIRECTORY_SEPARATOR . $this->filename);
		}
		return true;
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return OffersImages the static model class
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
		return 'offers_images';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('offer_id, filename', 'required'),
			array('offer_id', 'length', 'max'=>10),
			array('filename', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, offer_id, filename', 'safe', 'on'=>'search'),
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
			'offer' => array(self::BELONGS_TO, 'Offers', 'offer_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'offer_id' => 'Offer',
			'filename' => 'Filename',
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('offer_id',$this->offer_id,true);
		$criteria->compare('filename',$this->filename,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}