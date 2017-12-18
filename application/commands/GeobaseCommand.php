<?php

/**
 * Генерирует файлы для определния стран и городов по ip-адресу
 */
class GeobaseCommand extends CConsoleCommand
{
    public function actionIndex()
    {
        echo $this->getHelp();
    }

    /**
     * Строит файл определения городов по ip на основе файлов базы ipgeobase
     * В файлах ipgeobase сначала выбираются все города России, далее делается попытка поиска
     * города по названию в БД, если город не найден, то он добавляется в БД.
     * Идентификаторы найденных/добавленных в БД городов сопоставляются с ip-адресами городов и
     * сохраняются в выходной файл, который затем используется в модуле ngx_http_geo_module для определения
     * городов по ip-адресам.
     *
     * ГОРОДА И IP-АДРЕСА ОПРЕДЕЛЯЮТСЯ ТОЛЬКО ДЛЯ РОССИИ
     *
     * @param $cidr_optim_path
     * @param $cities_path
     * @param $output_path
     */
    public function actionCities($cidr_optim_path, $cities_path, $output_path)
    {
        $this->validateCitiesArgs($cidr_optim_path, $cities_path, $output_path);

        $geobaseCities = $this->getCities($cities_path);
        $geobaseIps    = $this->getIpRangesWithCities($cidr_optim_path);

        if (false === file_put_contents($output_path, $this->getCitiesOutput($geobaseCities, $geobaseIps))) {
            echo "Не удлось сохранить результат.";
            exit;
        }
    }

    /**
     * Ip-адреса для стран, которые есть в БД
     *
     * @param $cidr_optim_path
     * @param $output_path
     */
    public function actionCountries($cidr_optim_path, $output_path)
    {
        $this->validateCountriesArgs($cidr_optim_path, $output_path);

        $countriesCodes = Countries::model()->getAllCodes(false);
        if (!$countriesCodes) {
            echo "В БД не найдено ни одной страны";
            exit;
        }

        $pattern = "/(" . implode('|', $countriesCodes) . ")/";

        // Получаем список диапазонов ip-адресов и идентификаторов стран из БД
        exec("recode WINDOWS-1251..utf8 {$cidr_optim_path}");
        exec("cat {$cidr_optim_path} | awk '{if (match($6, " . $pattern . ")) print $3$4$5\" \"$6\";\"}' > {$output_path}");
    }

    /**
     * Проверяет переданные параметры для городов
     *
     * @param string $cidr_optim_path
     * @param string $cities_path
     * @param string $output_path
     */
    private function validateCitiesArgs($cidr_optim_path, $cities_path, $output_path)
    {
        if (!is_file($cities_path)) {
            echo "Не найден файл со списком городов\n";
            exit;
        }

        $this->validateCountriesArgs($cidr_optim_path, $output_path);
    }

    /**
     * Проверяет переданные параметры для стран
     *
     * @param string $cidr_optim_path
     * @param string $output_path
     */
    private function validateCountriesArgs($cidr_optim_path, $output_path)
    {
        if (!is_file($cidr_optim_path)) {
            echo "Не найден файл со списком ip-адресов\n";
            exit;
        }

        $outputDir = dirname($output_path);
        if (!is_dir($outputDir) || !is_writable($outputDir)) {
            echo "Директория '{$outputDir}' не существует или не доступна для записи\n";
            exit;
        }
    }

    /**
     * Возвращает массив идентификаторов городов и их диапазанов ip-адресов
     *
     * @param $cidr_optim_path
     * @return array
     */
    private function getIpRangesWithCities($cidr_optim_path)
    {
        // Получаем список диапазонов ip-адресов и идентификаторов городов для России
        exec("recode WINDOWS-1251..utf8 {$cidr_optim_path}");
        exec("cat {$cidr_optim_path} | awk '{if ($6 == \"RU\" && $7 != \"-\") print $3$4$5\";\"$7}'", $output);
        if (empty($output)) {
            echo "Не найдено ни одного ip-адреса";
            exit;
        }

        // Формируем выходной массив идентификаторов городов и их диапазонов ip-адресов
        $ips = array();
        foreach ($output as $str)
        {
            list($ipRange, $cityId) = explode(";", $str);

            if (!isset($ips[$cityId])) {
                $ips[$cityId] = array();
            }

            $ips[$cityId][] = $ipRange;
        }

        return $ips;
    }

    /**
     * Возвращает массив городов
     *
     * @param string $cities_path
     * @return array
     */
    private function getCities($cities_path)
    {
        // Получаем список городов России, игнорируем города Украины
        exec("recode WINDOWS-1251..utf8 {$cities_path}");
        exec("cat {$cities_path} | awk 'BEGIN {FS=\"\\t\"}; ! /Украина/ {print $1\";\"$3}'", $output);
        if (empty($output)) {
            echo "Не найдено ни одного города";
            exit;
        }

        // Формируем выходной массив идентификаторов городов и их названий
        $cities = array();
        foreach ($output as $str)
        {
            list($cityId, $name) = explode(";", $str);
            $cities[$cityId] = $name;
        }

        return $cities;
    }

    /**
     * Создает связь по идентификаторам городов из БД и диапазонам ip-адресов
     *
     * Если города не существует в БД, то он добавляется
     *
     * @param array $geobaseCities
     * @param array $geobaseIps
     *
     * @return string
     */
    private function getCitiesOutput(array $geobaseCities, array $geobaseIps)
    {
        $output = array();

        foreach ($geobaseIps as $cityId => $ranges)
        {
            $dbCityId = Cities::model()->getIdByName($geobaseCities[$cityId], 1);
            if ($dbCityId) {
                foreach ($ranges as $range) {
                    $output[] = $range . " " . $dbCityId;
                }
            }
        }

        return implode(";\r\n", $output) . ";";
    }
}