<?php

/**
 * Модель отчета по показам и кликам по тизеру и площадке за день
 *
 * The followings are the available columns in table 'report_daily_by_teaser_and_platform':
 * @property string $teaser_id
 * @property string $platform_id
 * @property string $date
 * @property integer $shows
 * @property integer $clicks
 */
class ReportDailyByTeaserAndPlatform extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportDailyByTeaserAndPlatform the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @return array Первичный ключ таблицы
     */
    public function primaryKey()
    {
        return array('teaser_id', 'platform_id', 'date');
    }

    /**
     * @return string Возвращает название таблицы
     */
    public function tableName()
    {
        return self::getTableName();
    }

    /**
     * @return string Возвращает название таблицы отчета
     */
    protected static function getTableName()
	{
		return 'report_daily_by_teaser_and_platform';
	}

    /**
     * Возвращает отчет по все тизерам рекламной площадки
     * на заданный период
     *
     * @param  integer $platform_id
     * @param  string  $date_from
     * @param  string  $date_to
     *
     * @return array
     */
    public function getForPlatform($platform_id, $date_from, $date_to)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('teaser_id, SUM(shows) as shows, SUM(clicks) as clicks');
        $command->from($this->tableName());
        $command->andWhere('platform_id = :id', array(':id' => $platform_id));
        $command->andWhere('date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->group('teaser_id');

        $result = array();
        foreach ($command->queryAll() as $dbRow) {
            $result[$dbRow['teaser_id']] = $dbRow;
        }

        if (!$result) return array();

        $teasers = Teasers::model()->getAllByIds(array_keys($result), $platform_id);
        if (!$teasers) return array();

        $reportData = array();
        foreach ($teasers as $teaser) {

            if (!isset($reportData[$teaser['campaign_id']])) {
                $reportData[$teaser['campaign_id']] = array(
                    'id'        => $teaser['campaign_id'],
                    'name'      => $teaser['campaign_name'],
                    'is_active' => 0,
                    'shows'     => 0,
                    'clicks'    => 0,
                    'ctr'       => 0,
                    'teasers'   => array()
                );
            }

            $shows  = isset($result[$teaser['id']]['shows']) ? (int) $result[$teaser['id']]['shows'] : 0;
            $clicks = isset($result[$teaser['id']]['clicks']) ? (int) $result[$teaser['id']]['clicks'] : 0;
            $ctr    = ($shows) ? ($clicks * 100 / $shows) : 0;

            $reportData[$teaser['campaign_id']]['teasers'][$teaser['id']] = array_merge($teaser, array(
                'shows'     => $shows,
                'clicks'    => $clicks,
                'ctr'       => sprintf('%.2f', round($ctr, 2)),
                'news_url_decoded' => IDN::decodeUrl($teaser['news_url']),
            ));

            $reportData[$teaser['campaign_id']]['teasers'][$teaser['id']]['is_active'] = $teaser['ct_except_is_active'];
            if($teaser['ct_except_is_active'] != 0){
                $reportData[$teaser['campaign_id']]['is_active'] = 1;
            }

            $reportData[$teaser['campaign_id']]['shows']  += $shows;
            $reportData[$teaser['campaign_id']]['clicks'] += $clicks;

            if ($reportData[$teaser['campaign_id']]['shows'] > 0) {
                $totalCtr = $reportData[$teaser['campaign_id']]['clicks'] * 100 / $reportData[$teaser['campaign_id']]['shows'];
                $reportData[$teaser['campaign_id']]['ctr'] = sprintf('%.2f', round($totalCtr, 2));
            }
        }

        return $reportData;
    }

    /**
     * Возвращает отчет по всем тизерам рекламной кампании
     * на заданный период
     *
     * @param  integer $campaign_id
     * @param  boolean $use_date
     * @param  string  $date_from
     * @param  string  $date_to
     * @param  string  $filter
     *
     * @return array
     */
    public function getForCampaign($campaign_id, $use_date = false, $date_from = null, $date_to = null, $filter = null)
    {
        if($use_date){
            $date_condition =  'AND r.date BETWEEN :date_from AND :date_to';
            $date_args = array( ':date_from' => $date_from, ':date_to' => $date_to );
        }else{
            $date_condition = '';
            $date_args = array();
        }

        $command = $this->getDbConnection()->createCommand();
        $command->select(array(
            'n.id as news_id',
            'n.name as news_name',
            'n.url as news_url',
            'n.url_status as news_url_status',
            'n.is_active as news_is_active',
            'n.description as news_description',
            'n.create_date as news_create_date',
            't.id',
            't.title',
            't.description',
            't.is_active',
            't.is_external',
            't.picture',
            't.create_date',
            'SUM(r.shows) as shows',
            'SUM(r.clicks) as clicks'
        ));
        $command->from(News::model()->tableName() . ' n');
        $command->leftjoin(Teasers::model()->tableName() . ' t', 'n.id = t.news_id');
        $command->leftJoin($this->tableName() . ' r', 't.id = r.teaser_id '.$date_condition, $date_args);
        $command->andWhere('n.campaign_id = :id', array(':id' => $campaign_id));
        $command->andWhere('n.is_deleted = 0');
        $command->andWhere('(t.is_deleted IS NULL OR t.is_deleted = 0)');
        if(isset($filter)){
            $command->andWhere("(n.name LIKE :filter OR n.url LIKE :filter OR n.id LIKE :filter OR n.description LIKE :filter OR n.id LIKE :filter OR t.id LIKE :filter)", array(':filter' => '%'.$filter.'%'));
        }
        $command->group('n.id, t.id');
        $result = $command->queryAll();

        $teaserTags = array();
        foreach($result as $teaser)
            if($teaser['id']) $teaserTags[] = $teaser['id'];
        $teaserTags = Teasers::model()->getTagNamesByIds($teaserTags);

        $reportData = array();
        foreach($result as $teaser) {
            $teaserIds[] = $teaser['id'];
            if (!isset($reportData[$teaser['news_id']])) {
                $reportData[$teaser['news_id']] = array(
                    'id'            => $teaser['news_id'],
                    'name'          => $teaser['news_name'],
                    'is_active'     => $teaser['news_is_active'],
                    'url'           => $teaser['news_url'],
                    'url_decoded'   => IDN::decodeUrl($teaser['news_url']),
                    'url_status'    => $teaser['news_url_status'],
                    'description'   => $teaser['news_description'],
                    'create_date'   => $teaser['news_create_date'],
                    'shows'         => 0,
                    'clicks'        => 0,
                    'ctr'           => 0,
                    'teasers'       => array()
                );
            }

            $shows  = isset($teaser['shows']) ? (int) $teaser['shows'] : 0;
            $clicks = isset($teaser['clicks']) ? (int) $teaser['clicks'] : 0;
            $ctr    = ($shows) ? ($clicks * 100 / $shows) : 0;

            if(isset($teaser['id'])){
                $reportData[$teaser['news_id']]['teasers'][$teaser['id']] = array_merge($teaser, array(
                    'shows'     => $shows,
                    'clicks'    => $clicks,
                    'ctr'       => sprintf('%.2f', round($ctr, 2)),
                    'tag_names'      => isset($teaserTags[$teaser['id']]) ? $teaserTags[$teaser['id']] : array(),
                ));
            }

            $reportData[$teaser['news_id']]['shows']  += $shows;
            $reportData[$teaser['news_id']]['clicks'] += $clicks;

            if ($reportData[$teaser['news_id']]['shows'] > 0) {
                $totalCtr = $reportData[$teaser['news_id']]['clicks'] * 100 / $reportData[$teaser['news_id']]['shows'];
                $reportData[$teaser['news_id']]['ctr'] = sprintf('%.2f', round($totalCtr, 2));
            }
        }
        unset($result);

        return $reportData;
    }

    public function getForCorrection($campaign_id, $date_from, $date_to, $teaser_id, $platform_id, $group = false)
    {
        $command = $this->getDbConnection()->createCommand()
            ->select(array(
                'date',
                'teaser_id',
                'platform_id',
                't.news_id',
                't.title as teaser_title',
                'p.server as platform_server',
                'r.shows',
                'r.clicks',
            ))
            ->from($this->tableName() . ' r')
            ->leftJoin(Teasers::model()->tableName() . ' t', 'r.teaser_id = t.id')
            ->leftJoin(News::model()->tableName() . ' n', 'n.id = t.news_id')
            ->leftJoin(Platforms::model()->tableName() . ' p', 'p.id = r.platform_id')
            ->where('n.campaign_id = :campaign_id', array(':campaign_id' => $campaign_id))
            ->andWhere('date BETWEEN :date_from AND :date_to', array(':date_from' => $date_from, ':date_to' => $date_to))
            ->andWhere('r.clicks > 0 OR r.shows > 0')
            ->order('date, news_id, teaser_id, platform_id');
        if(!empty($teaser_id)){
            $command->andWhere(
                $this->getDbConnection()->commandBuilder
                    ->createInCondition($this->getTableSchema(), 'teaser_id', (array) $teaser_id, 'r.')
            );
        }
        if(!empty($platform_id)){
            $command->andWhere(
                $this->getDbConnection()->commandBuilder
                    ->createInCondition($this->getTableSchema(), 'platform_id', (array) $platform_id, 'r.')
            );
        }
//        return $command->queryAll();
        $result = array(
            'rows' => array(),
            'total' => array(
                'clicks' => 0,
                'shows' => 0,
            )
        );
        foreach($command->queryAll() as $dbRow){
            if($group){
                if(!isset($result['rows'][$dbRow['teaser_id']])){
                    $result['rows'][$dbRow['teaser_id']] = array(
                        'teaser_title' => $dbRow['teaser_title'],
                        'news_id' => $dbRow['news_id'],
                    );
                }

                if(!isset($result['rows'][$dbRow['teaser_id']]['platforms'][$dbRow['platform_id']])){
                    $result['rows'][$dbRow['teaser_id']]['platforms'][$dbRow['platform_id']] = array(
                        'platform_server' => $dbRow['platform_server'],
                        'shows' => 0,
                        'clicks' => 0,
                    );
                }
                $result['rows'][$dbRow['teaser_id']]['platforms'][$dbRow['platform_id']]['shows'] += $dbRow['shows'];
                $result['rows'][$dbRow['teaser_id']]['platforms'][$dbRow['platform_id']]['clicks'] += $dbRow['clicks'];
            }else {
                $result['rows'][] = $dbRow;
            }
            $result['total']['clicks'] += $dbRow['clicks'];
            $result['total']['shows'] += $dbRow['shows'];
        }

        return $result;
    }

    public function getTotalsByTeaserId($teaserId)
    {
        return $this->getDbConnection()->createCommand()
            ->select(array(
                'SUM(shows) AS shows',
                'SUM(clicks) AS clicks'
            ))
            ->from($this->getTableName())
            ->where('teaser_id = :teaser_id', array(':teaser_id' => $teaserId))
            ->queryRow();
    }
}