<?

/**
 * Склеивает площадки в одну
 */
class ReducePlatformsCommand extends CConsoleCommand
{
    /**
     * @var array Список отчетов, по которым необходимо перенести данные
     */
    private $reports = array(
        'ReportDailyByCampaignAndPlatform',
        'ReportDailyByNewsAndPlatform',
        'ReportDailyByPlatform',
        'ReportDailyByTeaserAndPlatform',
    );

    public function actionIndex($server, $stat = 1, $delete = 0)
    {
        $platform = $this->getPlatform($server);
        $reducingPlatforms = $this->getReducingPlatforms($platform);

        if ($stat) {
            foreach ($reducingPlatforms as $source) {
                $this->moveReportData($source, $platform);
            }
            echo "Обработано площадок " . count($reducingPlatforms) . "\n";
        }

        if ($delete) {
            foreach ($reducingPlatforms as $source) {
                $source->is_deleted = 1;
                $source->save(false, array('is_deleted'));
            }

            echo "Удалено площадок : " . count($reducingPlatforms) . "\n";
        }
    }

    /**
     * Возвращает площадку по названию сервера
     *
     * @param string $server
     *
     * @return Platforms
     */
    private function getPlatform($server)
    {
        $server = trim(strtolower($server));

        $platforms = Platforms::model()->findAllByAttributes(array(
            'server'    => $server
        ));

        if (empty($platforms)) {
            echo "Не найдено ни одной площадки с server='{$server}'.\n";
            die();
        } elseif (count($platforms) > 1) {
            echo "Найдено более одной платформы с server='{$server}'.\n";
            die();
        }

        return $platforms[0];
    }

    /**
     * Возвращает площадки, который будут склеены
     *
     * @param Platforms $platform
     *
     * @return Platforms[]
     */
    private function getReducingPlatforms(Platforms $platform)
    {
        $hosts = $platform->getHostsAsArray();
        if (count($hosts) == 1) {
            echo "Среди списка хостов не найдено других площадок.\n";
            die();
        }

        $reducingPlatforms = Platforms::model()->findAllByAttributes(
            array('server' => $hosts, 'is_deleted' => 0),
            'id <> :platform_id',
            array('platform_id' => $platform->id)
        );

        if (empty($reducingPlatforms)) {
            echo "Не найдено ни одной платформы для сворачивания данных.\n";
            die();
        }

        return $reducingPlatforms;
    }


    private function moveReportData(Platforms $source, Platforms $destination)
    {
        foreach ($this->reports as $report)
        {
            $sql = $this->buildSql($report, $source->id, $destination->id);
            $command = Yii::app()->db->createCommand($sql);
            $command->execute();
        }
    }

    /**
     * Создает sql-запрос для перноса статистики
     *
     * @param $reportName
     * @param $source_id
     * @param $destination_id
     *
     * @return string
     */
    private function buildSql($reportName, $source_id, $destination_id)
    {
        $report = Report::model($reportName);

        $sql  = "INSERT INTO {table} ({pk}, platform_id, shows, clicks) ";
        $sql .= "SELECT {pk}, {distanation_id} as platform_id, r.shows, r.clicks FROM {table} r WHERE platform_id = {source_id} ";
        $sql .= "ON DUPLICATE KEY UPDATE {table}.shows = {table}.shows + r.shows, {table}.clicks = {table}.clicks + r.clicks";

        $pk = implode(', ', array_filter($report->primaryKey(), function($var) { return ($var != 'platform_id'); }));

        return str_replace(
            array('{table}', '{pk}', '{distanation_id}', '{source_id}'),
            array($report->tableName(), $pk, $destination_id, $source_id),
            $sql
        );
    }
}