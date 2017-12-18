<?php
class UserColumn
{
	public static function get($data, $only_active = false, $clientsList = true)
	{
		switch($data->role){
			case 'webmaster':
				return self::getDataWebmaster($data, $only_active, $clientsList);
			case 'platform':
				return self::getDataPlatform($data, $only_active, $clientsList);
			case 'admin':
				return self::getDataAdmin($data, $only_active, $clientsList);
			case 'user':
				return self::getDataUser($data, $only_active, $clientsList);
		}
		
		return '';
	}

	public static function getDataWebmaster($data, $only_active = false, $clientsList = true)
	{
		$content = '<table class="table table-campaign-header">
				    <tbody>
				    <tr>
				        <th class="campaign-logo-cell"><img alt="'.$data->login.'" src="/i/c/'.(strlen($data->logo)?$data->logo:'default.jpg').'"></th>
				        <th class="campaign-owner-cell">
				            <div class="campaign-owner"><a href="'.$data->id.'" class="user-story-title inline"><i class="icon-16 icon-cog"></i></a><a href="'.Yii::app()->createUrl('/users/'.$data->id).'">'.$data->login.'</a></div>
				            <a href="mailto:'.$data->email.'">'.$data->email.'</a>
				        </th>
				        <th class="campaign-addnew-cell">
				        	'.$data->getRoleName().'
				        </th>
				    </tr>
				    </tbody>
				</table>';
		
		Offers::disableDefaultScope();
		$status = Yii::app()->request->getParam('status', -1);
		$offers = OffersUsers::model()->findByUserId($data->id, true, $status === '' ? -1 : $status);
		
		$gv = Yii::app()->controller->widget('zii.widgets.grid.CGridView', array(
				'id'=>'campaigns-grid-'.$data->id,
				'dataProvider'=>$offers,
				'template'=> '{items}{pager}',
				'cssFile' => false,
				'htmlOptions' => array('class' => 'table table-striped table-bordered table-hover table-shadow table-campaign tu-'.$data->id),
				'columns'=>array(
						array(
								'name' => 'id',
								'header' => 'ID',
								'type' => 'raw',
								'value' => '$data["id"]',
								'htmlOptions' => array('class' => 'align-left'),
						),
						array(
								'name' => 'offer_name',
								'header' => 'Кампания',
								'type' => 'raw',
								'value' => '(!empty($data["offer"]->campaign->client->logo)?'
								.'"<img src=\"/i/c/".$data["offer"]->campaign->client->logo."\" width=\"50\" height=\"50\" style=\"float: left; margin-right: 10px;\"/>"'
								.':"<img src=\"/i/c/no_image.png\" width=\"50\" height=\"50\" style=\"float: left; margin-right: 10px;\"/>")'
								.'.($data["is_deleted"] ? $data["offer"]->name : ("<a href=\"".Yii::app()->createUrl("/offers/".$data["offer"]->id)."\" class=\"view-offer\" data-id=\"".$data["offer"]->id."\">".$data["offer"]->name."</a>"))',
								'htmlOptions' => array('class' => 'align-left'),
						),
						array(
								'name' => 'countries',
								'header' => 'Страны',
								'type' => 'raw',
								'value' => '($data["offer"]->getCountriesCodes()) ? $data["offer"]->getCountriesCodes() : " -- "',
								'htmlOptions' => array()
						),
						array(
								'name' => 'price',
								'header' => 'Цена за действие',
								'value' => 'Yii::app()->numberFormatter->formatDecimal($data["offer"]->reward)." руб."'
						),
						array(
								'name' => 'offers_clicks',
								'header' => 'Клики',
								'value' => '$data["offers_clicks"]'

						),
						array(
								'name' => 'offers_actions',
								'header' => 'Действия',
								'value' => 'Yii::app()->numberFormatter->formatDecimal($data["offers_actions"])'
						),
						array(
								'name' => 'offers_moderation_actions',
								'header' => 'В ожидании',
								'value' => 'Yii::app()->numberFormatter->formatDecimal($data["offers_moderation_actions"])'
						),
						array(
								'name' => 'offers_declined_actions',
								'header' => 'Отклонено',
								'value' => 'Yii::app()->numberFormatter->formatDecimal($data["offers_declined_actions"])'
						),
						array(
								'name' => 'conversions',
								'header' => 'Конверсии, %',
								'value' => 'Yii::app()->numberFormatter->formatDecimal($data["conversions"])'
						),
						array(
								'name' => 'total',
								'header' => 'Всего',
								'value' => 'Yii::app()->numberFormatter->formatDecimal($data["reward_total"])." руб."'
						),
						array(
								'name' => 'status',
								'header' => 'Статус',
								'value' => '$data->getStatusName()'
						),
				),
		), true);
		
		return $content.$gv;
	}

	public static function getDataPlatform($data, $only_active = false, $clientsList = true)
	{
		$content = '<table class="table table-campaign-header">
				    <tbody>
				    <tr>
				        <th class="campaign-logo-cell"><img alt="'.$data->login.'" src="/i/c/'.(strlen($data->logo)?$data->logo:'default.jpg').'"></th>
				        <th class="campaign-owner-cell">
				            <div class="campaign-owner"><a href="'.$data->id.'" class="user-story-title inline"><i class="icon-16 icon-cog"></i></a><a href="'.Yii::app()->createUrl('/users/'.$data->id).'">'.$data->login.'</a></div>
				            <a href="mailto:'.$data->email.'">'.$data->email.'</a>
				        </th>
				        <th class="campaign-addnew-cell">
				        	'.$data->getRoleName().'
				       	</th>
				    </tr>
				    </tbody>
				</table>';
		
		$model = new Platforms('search');
		$model->user_id = $data->id;
		$sort = isset($_GET['Platforms_sort'])?$_GET['Platforms_sort']:'';
		$gv = Yii::app()->controller->widget('zii.widgets.grid.CGridView', array(
				'id'=>'campaigns-grid-'.$data->id,
				'dataProvider'=>$model->search(),
				'template'=> '{items}{pager}',
				'cssFile' => false,
				'htmlOptions' => array('class' => 'table table-striped table-bordered table-hover table-shadow table-campaign tu-'.$data->id),
				'columns'=>array(
						array(
								'type'=>'raw',
								'value'=>'$data->id',
								'header'=>'ID',
						),
						array(
								'name' => 'server',
								'type'=>'raw',
								'value'=>'"<a href=\'".Yii::app()->createUrl("/platforms/news/".$data->id)."\'>".$data->server."</a>"',
								'header' => 'Сервер<i class="icon-sort'.($sort == 'server'?' icon-sort-down':($sort == 'server.desc'?' icon-sort-up':'')).'"></i>',
						),
						array(
								'name' => 'id',
								'header' => 'ID<i class="icon-sort'.($sort == 'id'?' icon-sort-down':($sort == 'id.desc'?' icon-sort-up':'')).'"></i>',
								'visible' => (Yii::app()->user->role === Users::ROLE_ADMIN)
						),
						array(
								'header' => 'Активность<i class="icon-sort'.($sort == 'is_active'?' icon-sort-down':($sort == 'is_active.desc'?' icon-sort-up':'')).'"></i>',
								'name' => 'is_active',
								'type'=>'raw',
								'value'=>'$data->is_active == 1 ? "<i class=\'icon-12 icon-status-green\'></i>" : "<i class=\'icon-12 icon-status-red\'></i>"'
						)
						,array(
								'header' => 'Внешняя сеть<i class="icon-sort'.($sort == 'is_external'?' icon-sort-down':($sort == 'is_external.desc'?' icon-sort-up':'')).'"></i>',
								'name' => 'is_external',
								'type'=>'raw',
								'value'=>'$data->is_external == 1 ? "Да" : "Нет"',
								'visible' => Yii::app()->user->role === Users::ROLE_ADMIN,
						),
						array(
								'header' => 'Сегмент<i class="icon-sort'.($sort == 'tag_names'?' icon-sort-down':($sort == 'tag_names.desc'?' icon-sort-up':'')).'"></i>',
								'name' => 'tag_names',
						),
		
				)
		), true);
		
		return $content.$gv;
	}

	public static function getDataAdmin($data, $only_active = false, $clientsList = true)
	{
		$content = '<table class="table table-campaign-header">
				    <tbody>
				    <tr>
				        <th class="campaign-logo-cell"><img alt="'.$data->login.'" src="/i/c/'.(strlen($data->logo)?$data->logo:'default.jpg').'"></th>
				        <th class="campaign-owner-cell">
				            <div class="campaign-owner"><a href="'.$data->id.'" class="user-story-title inline"><i class="icon-16 icon-cog"></i></a><a href="'.Yii::app()->createUrl('/users/'.$data->id).'">'.$data->login.'</a></div>
				            <a href="mailto:'.$data->email.'">'.$data->email.'</a>
				        </th>
				        <th class="campaign-addnew-cell">
				        	'.$data->getRoleName().'
				       	</th>
				    </tr>
				    </tbody>
				</table>';
		
		$gv = '';
		
		return $content.$gv;
	}

	public static function getDataUser($data, $only_active = false, $clientsList = true)
	{
		$content = '<table class="table table-campaign-header">
				    <tbody>
				    <tr>
				        <th class="campaign-logo-cell"><img alt="'.$data->login.'" src="/i/c/'.(strlen($data->logo)?$data->logo:'default.jpg').'"></th>
				        <th class="campaign-owner-cell">
				            <div class="campaign-owner"><a href="'.$data->id.'" class="user-story-title inline"><i class="icon-16 icon-cog"></i></a><a href="'.Yii::app()->createUrl('/users/'.$data->id).'">'.$data->login.'</a></div>
				            <a href="mailto:'.$data->email.'">'.$data->email.'</a>
				        </th>'.($clientsList ? ('
				        <th class="campaign-addnew-cell">
				            <a class="addCompany" data-user-id="'.$data->id.'" href="'.Yii::app()->createUrl('campaigns/edit?u='.$data->id).'"><i class="icon-16 icon-addad"></i> Добавить рекламную кампанию</a>
				        </th>') : '
				        <th class="campaign-addnew-cell">
				        	'.$data->getRoleName().'
				       	</th>
				        ').'
				    </tr>
				    </tbody>
				</table>';
		
		$model = new Campaigns();
		
		if(isset($_GET[get_class($model)])){
			$model->attributes = $_GET[get_class($model)];
			if(isset($_GET[get_class($model)]['is_active']) && $_GET[get_class($model)]['is_active'] == 1){
				$only_active = true;
			}
		}
		
		//		var_dump($model->sort_sort);die();
		$sort = isset($_GET['Campaigns_sort'])?$_GET['Campaigns_sort']:'';
		$gv = Yii::app()->controller->widget('zii.widgets.grid.CGridView', array(
				'id'=>'campaigns-grid-'.$data->id,
				'dataProvider'=>$model->searchForUser($data->id, $only_active),
				'template'=> '{items}{pager}',
				'cssFile' => false,
				'htmlOptions' => array('class' => 'table table-striped table-bordered table-hover table-shadow table-campaign tu-'.$data->id),
				'columns'=>array(
						array(
								'name' => 'name',
								'type'=>'raw',
								'header' => 'Название кампании<i class="icon-sort'.($sort == 'name'?' icon-sort-down':($sort == 'name.desc'?' icon-sort-up':'')).'"></i>',
								'value' => '"<a href=\'". Yii::app()->createUrl("/campaigns/".$data->id)."\'>".$data->name."</a>"'
		
						),
						array(
								'name' => 'id',
								'header' => 'ID<i class="icon-sort'.($sort == 'id'?' icon-sort-down':($sort == 'id.desc'?' icon-sort-up':'')).'"></i>',
								'headerHtmlOptions' => array('class' => 'min'),
						),
						array(
								'name' => 'clicks',
								'header' => 'Переходов<i class="icon-sort'.($sort == 'clicks'?' icon-sort-down':($sort == 'clicks.desc'?' icon-sort-up':'')).'"></i>',
								'headerHtmlOptions' => array('class' => 'min'),
								'class'=>'DataColumn',
								'evaluateHtmlOptions'=>true,
								'htmlOptions'=>array('colspan' => '$data->getGlobalIsActive() ? "1":"4"'),
								'type' => 'raw',
								'value'=>'$data->getGlobalIsActive() ? $data->totalClicks():"Неактивна <a class=\"clone-campaign\" href=\"".Yii::app()->createUrl("campaigns/returnForm", array("update_id" => $data->id, "clone" => 1))."\"><i class=\"icon-retweet\"></i> </a>"'
						),
						array(
								'name' => 'actions',
								'header' => 'Действий<i class="icon-sort'.($sort == 'actions'?' icon-sort-down':($sort == 'actions.desc'?' icon-sort-up':'')).'"></i>',
								'headerHtmlOptions' => array('class' => 'min'),
								'class'=>'DataColumn',
								'type' => 'raw',
								'value'=>'$data->getGlobalIsActive() && $data->cost_type == Campaigns::COST_TYPE_ACTION ? $data->actions:"-"',
								'evaluateHtmlOptions'=>true,
								'htmlOptions'=>array('style' => '"display:".($data->getGlobalIsActive()?"table-cell":"none")'),
						),
						array(
								'name' => 'bounce_rate_diff',
								'header' => 'Отказы<i class="icon-sort'.($sort == 'bounce_rate_diff'?' icon-sort-down':($sort == 'bounce_rate_diff.desc'?' icon-sort-up':'')).'"></i>',
								'headerHtmlOptions' => array('class' => 'bounce-rate'),
								'evaluateHtmlOptions'=>true,
								'htmlOptions'=>array('style' => '"display:".($data->getGlobalIsActive()?"table-cell":"none")'),
								'type' => 'raw',
								'value' => '$data->getBounceRateHtml()',
								'class'=>'DataColumn',
						),
						array(
								'name' => 'days_left',
								'class'=>'DataColumn',
								'evaluateHtmlOptions'=>true,
								'header' => 'Осталось дней<i class="icon-sort'.($sort == 'days_left'?' icon-sort-down':($sort == 'days_left.desc'?' icon-sort-up':'')).'"></i>',
								'headerHtmlOptions' => array('class' => 'min'),
								'htmlOptions'=>array('style' => '"display:".($data->getGlobalIsActive()?"table-cell":"none")'),
						),
				),
		), true);
		
		return $content.$gv;
	}
}