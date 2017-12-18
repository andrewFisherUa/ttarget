<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/main';
	public $userData=null;
	public $billingFilter=false;
    public $adaptive = false;

	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

    public $notifications = 0;
    public $offers = 0;

	public function init(){
        $this->notifications = Notifications::model()->getNewCount();
        $this->offers = OffersUsers::model()->getCountNew();
        if(in_array(Yii::app()->user->role, array(Users::ROLE_PLATFORM, Users::ROLE_WEBMASTER), true)){
            $this->userData = Users::model()->findByPk(Yii::app()->user->id);
        }
		parent::init();
	}

    /**
     * don't reload these scripts or they will mess up the page
     * yiiactiveform.js still needs to be loaded that's why we don't use
     * Yii::app()->clientScript->scriptMap['*.js'] = false;
     */
    protected function disableClientScripts()
    {
        Yii::app()->clientScript->scriptMap = array(
            'jquery.min.js' => false,
            'jquery.js' => false,
            'jquery.fancybox-1.3.4.js' => false,
            'jquery.fancybox.js' => false,
            'jquery-ui-1.8.12.custom.min.js' => false,
            'json2.js' => false,
            'jquery.form.js' => false,
            'form_ajax_binding.js' => false
        );
    }

    /**
     * @param array $data
     */
    protected function renderJsonAndExit($data)
    {
        header('Content-type: application/json');
        echo CJSON::encode($data);
        Yii::app()->end();
    }
}