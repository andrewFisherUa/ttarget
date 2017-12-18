<? /** @var $this Controller */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="language" content="en"/>

    <link href="<?= Yii::app()->request->baseUrl; ?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&subset=latin,cyrillic' rel='stylesheet'
          type='text/css'>
    <link href="<?= Yii::app()->request->baseUrl; ?>/css/icons.css" rel="stylesheet" media="screen">
    <link href="<?= Yii::app()->request->baseUrl; ?>/css/style.css" rel="stylesheet" media="screen">
	<link rel="stylesheet" href="/css/jquery-ui.css">
	
    <title><?= CHtml::encode($this->pageTitle); ?></title>

    <script type="text/javascript">
        var CSRF_TOKEN = '<?= Yii::app()->request->csrfToken ?>';
    </script>

    <script src="<?= Yii::app()->request->baseUrl; ?>/js/bootstrap.min.js"></script>
    <script src="<?= Yii::app()->request->baseUrl; ?>/js/backtotop.js"></script>
    <script src="<?= Yii::app()->request->baseUrl; ?>/js/script.js"></script>
    <script src="<?= Yii::app()->request->baseUrl; ?>/js/jquery-ui.min.js"></script>
</head>

<body>
<div id="header">
    <div class="page-header">
        <div class="page-header-row page-header-row1">
            <div class="page-header-row-inner">
                <ul class="nav-top">
                    <? if (!Yii::app()->user->isGuest) { ?>
                        <? if (Yii::app()->user->role == 'admin') { ?>
                            <li>
                                <a class="header-link-logout" href="<?= $this->createUrl('/site/logout'); ?>">
                                    <i class="icon-16 icon-signout"></i> Выход
                                </a>
                            </li>
                            <li><a href="<?= Yii::app()->user->id; ?>" class="user-story-title">
                                <i class="icon-16 icon-cog"></i><strong><?= Yii::app()->user->name; ?></strong>
                            </a></li>
                            <li><a class="header-link-settings<? if ($this->getId() == 'options') {
                                    echo ' active';
                                } ?>" href="<?= $this->createUrl('/options'); ?>"><i class="icon-16 icon-cog"></i>
                                    Настройки</a></li>
                        <? }else{ ?>
                            <li>
                                <a class="header-link-logout" href="<?= $this->createUrl('/site/logout'); ?>">
                                    <i class="icon-16 icon-signout"></i> Выход (<?= Yii::app()->user->name; ?>)
                                </a>
                            </li>
                        <? } ?>
                    <? } else { ?>
                        <li><a class="header-link-logout" href="<?= $this->createUrl('/site/login') ?>"><i
                                    class="icon-16 icon-signin"></i> Вход </a></li>
                    <? } ?>
                </ul>
                <h2 class="site-title"><?= CHtml::encode(Yii::app()->name); ?></h2>
            </div>
        </div>

        <div class="page-header-row page-header-row2" style="width: 100%; text-align: center;">
            <div class="page-header-row-inner" style="display: inline-block;">
                <div class="navbar">
                    <div class="navbar-inner">
                        <? $this->widget('zii.widgets.CMenu', array(
                            'htmlOptions' => array('class' => 'nav'),
                            'activeCssClass' => 'active',
                            'encodeLabel' => false,
                            'items' => array(
                                array('label' => 'Главная', 'active' => ($this->getId() == 'site' ? true : false), 'url' => array('site/index'), 'visible' => in_array(Yii::app()->user->role, array(Users::ROLE_USER, Users::ROLE_ADMIN, Users::ROLE_PLATFORM, Users::ROLE_WEBMASTER), true)),
                                array('label' => 'Клиенты', 'active' => ($this->getId() == 'users' && $this->getAction()->getId() == 'clients' ? true : false), 'url' => array('users/clients'), 'visible' => (Yii::app()->user->role == 'admin')),
                                array('label' => 'Рекламные кампании', 'active' => ($this->getId() == 'campaigns' ? true : false), 'url' => array('campaigns/admin'), 'visible' => (Yii::app()->user->role == Users::ROLE_USER)),
                                array('label' => 'Рекламные площадки', 'active' => ($this->getId() == 'platforms' ? true : false), 'url' => array('platforms/admin'), 'visible' => (Yii::app()->user->role == Users::ROLE_ADMIN || Yii::app()->user->role == Users::ROLE_PLATFORM)),
                                array('label'=> 'Активность '.($this->notifications > 0 ? '<span class="nav-num-notifications" id="notification_cnt_new">'.$this->notifications.'</span>' : ''),'active'=>($this->getId() == 'notifications' ? true: false), 'url'=>array('/notifications'), 'visible'=>(Yii::app()->user->role == Users::ROLE_ADMIN)),
                                array('label'=> 'Предложения ', 'active' => ($this->getId() == 'offers' && $this->getAction()->getId() != 'stats' ? true: false), 'url'=>array('/offers/list'), 'visible'=>(Yii::app()->user->role == Users::ROLE_WEBMASTER)),
                                array('label'=> 'Заявки'.($this->offers > 0 ? '<span class="nav-num-notifications" id="offers_cnt_new">'.$this->offers.'</span>' : ''),'active'=>($this->getId() == 'offers' ? true: false), 'url'=>array('/offers/requests'), 'visible'=>(Yii::app()->user->role == Users::ROLE_ADMIN)),
                                array('label'=> 'Статистика','active'=>($this->getId() == 'offers' && $this->getAction()->getId() == 'stats' ? true: false), 'url'=>array('/offers/stats'), 'visible'=>(Yii::app()->user->role == Users::ROLE_WEBMASTER)),
                            	array('label'=> 'Пользователи','active'=>($this->getId() == 'users' && $this->getAction()->getId() == 'index' ? true: false), 'url'=>array('/users'), 'visible'=>(Yii::app()->user->role == Users::ROLE_ADMIN)),
                            	array('label'=> 'Биллинг','active'=>($this->getId() == 'billing' ? true: false), 'url'=>array('/billing'), 'visible'=> in_array(Yii::app()->user->role, array(Users::ROLE_ADMIN, Users::ROLE_PLATFORM, Users::ROLE_WEBMASTER), true)),
                                array('label'=> 'Конструктор','active'=>($this->getId() == 'options' ? true: false), 'url'=>array('/options/constructor'), 'visible'=>(Yii::app()->user->role == Users::ROLE_PLATFORM)),
                            	array('label'=> 'Мой аккаунт','active'=>($this->getId() == 'users' ? true: false), 'url'=>array('/users/account'), 'visible'=>(Yii::app()->user->role == Users::ROLE_WEBMASTER)),
                                array('label'=> 'Сегменты','active'=>($this->getId() == 'segments' ? true: false), 'url'=>array('/segments/admin'), 'visible'=> Yii::app()->user->role == Users::ROLE_ADMIN),
                                array('label'=> 'Страницы','active'=>($this->getId() == 'pages' ? true: false), 'url'=>array('/pages/admin'), 'visible'=> Yii::app()->user->role == Users::ROLE_ADMIN),
                            ),
                        )); ?>
                    </div>
                </div>
            </div>
        </div>

        <?if ($this->userData && (Yii::app()->user->role != 'user')) {?>
        	<?if(Yii::app()->user->role == Users::ROLE_ADMIN && Yii::app()->controller->id == 'users' && Yii::app()->controller->action->id == 'view'):?>
        	
        	<?else:?>
            <div class="page-header-row page-header-row3">
                <div class="page-header-row-inner">
                    <table class="table table-campaign-header">
                        <tbody>
                        <tr>
                            <th class="campaign-logo-cell"><img alt="<?= $this->userData->login ?>"
                                                                src="/i/c/<?= strlen($this->userData->logo) ? $this->userData->logo : 'default.jpg'; ?>">
                            </th>
                            <th class="campaign-owner-cell">
                                <div class="campaign-owner"><?= $this->userData->login ?> &nbsp;<a href="<?= $this->userData->id ?>" class="user-story-title inline"><i class="icon-pencil"></i></a></div>
								<a href="mailto:<?= $this->userData->email ?>"><?= $this->userData->email ?></a></th>
                                <? if ($this->userData->campaignsCount()) { ?>
                                <th class="campaign-num-cell ">
                                    <a href="<?= $this->createUrl('/users/' . $this->userData->id) ?>">Рекламные кампании
                                        (<?= $this->userData->campaignsCount() ?>)</a>
                                </th>
                                <? } ?>
                                <? if ($this->userData->platformsCount()) { ?>
                                    <th class="campaign-num-cell">
                                        <div class="text-center"><a href="<?= $this->createUrl('/platforms/admin', array('Platforms[user_id]' => $this->userData->id)); ?>"> Площадки (<? echo $this->userData->platformsCount(); ?>)</a></div>
                                    </th>
                                    <? if (Yii::app()->user->role !== Users::ROLE_PLATFORM){ ?>
                                       
                                        
                                        <th class="campaign-addnew-cell">
                                            <a id="platform-add" href=""><i class="icon-16 icon-addad"></i> Добавить площадку</a>
                                        </th>
                                    <? } ?>
                                <? } elseif(Yii::app()->user->role == Users::ROLE_USER) { ?>
									<th class="campaign-addnew-cell">
                                        <a id="addCompany"
                                           href="<?= Yii::app()->createUrl('/campaigns/returnForm?u=' . $this->userData->id) ?>"><i
                                                class="icon-16 icon-addad"></i> Добавить рекламную кампанию</a>
                                    </th>
                                <? } ?>

                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?endif;?>
            <script type="text/javascript">
                $(function () {

                    $('#addCompany').each(function (index) {
                        //var id = $(this).data('id');
                        $(this).bind('click', function () {
                            $.ajax({
                                type: "POST",
                                url: $(this).attr('href'),
                                data: {/*"update_id":id,*/"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
                                beforeSend: function () {
                                    $("#users-grid").addClass("ajax-sending");
                                },
                                complete: function () {
                                    $("#users-grid").removeClass("ajax-sending");
                                },
                                success: function (data) {
                                    $.fancybox(data,
                                        $.extend({}, fancyDefaults, {
                                            "width": 543,
                                            "minWidth": 543,
                                            "afterClose": function () {
                                                window.location.href = window.location.href;
                                            } //onclosed function
                                        })
                                    );//fancybox
                                    //  console.log(data);
                                } //success
                            });//ajax
                            return false;
                        });
                    });
                });

                var facyboxClose = function () {
                    $.fancybox.close();
                    return false;
                }
            </script>
        <? } ?>
        <? if ($this->billingFilter) { ?>
            <div class="page-header-row page-header-row3">
                <div class="page-header-row-inner">
                    <table class="table table-billing-header table-bordered">
                        <tbody>
                        <tr>
                            <th><a class="separate-billing" data-bill="out" href="/"><i
                                        class="icon-16 icon-billing-out"></i> Исходящие счета</a></th>
                            <th><a class="separate-billing" data-bill="in" href="/"><i
                                        class="icon-16 icon-billing-in"></i> Входящие счета</a></th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <script type="text/javascript">
                $(function () {
                    $('a.separate-billing').click(function () {
                        $('.section-billing').hide();
                        $('#bill-' + $(this).data('bill')).show();
                        return false;
                    });
                });
            </script>
        <? } ?>
    </div>


    <? /* if(isset($this->breadcrumbs)):?>
		<? $this->widget('zii.widgets.CBreadcrumbs', array(
			'links'=>$this->breadcrumbs,
		)); ?><!-- breadcrumbs -->
	<? endif*/
    ?>
    <div class="container<?= $this->adaptive ? " adaptive" : ""; ?>">
        <?= $content; ?>
        <div class="page-footer">
            Copyright &copy; <?= date('Y'); ?> by (c) tTarget.<br>
            All Rights Reserved.<br/>
            Версия приложения 2.0.0
        </div>
    </div>
    <a href="/" id="top-link" title="К верху страницы"></a>
</body>
</html>
