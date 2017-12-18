<?php

/**
 * Удаляет новость из бд и редиса
 */
class CampaignCheckNewsUrlJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array('campaign_id' => Campaigns::$id)
     *
     */
    public $args = array();

    public function perform()
    {
        if(!$this->canPerform()){
            return;
        }

        $news = $this->getNews();
        if(empty($news)){
            return;
        }

        $urls = array();
        foreach($news as $news){
            if(!empty($news->url)){
                $urls[$news->buildUrl(array(), false)][] = $news;
            }
        }

        $c = new Connection(array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_NOBODY => true,
            CURLOPT_MAXREDIRS => 1,
        ));

        foreach($urls as $url => $news){
            $c->addRequest(new ConnectionRequest($url));
        }
        $requests = $c->run();

        foreach($requests as $i => $request){
            foreach($urls[$request->url] as $news){
                $result = ($request->reply->result == 0 ? $request->reply->info['http_code'] : $request->reply->result);
                /** @var News $news */
                if($news->url_status != $result){
                    $news->updateByPk($news->id, array('url_status' => $result));
                    if($result !== 200){
                        SMail::sendMail(
                            Yii::app()->params->notifyEmail,
                            'Новость "'.$news->name.'" не доступна.',
                            'NewsUrlCheckError',
                            array(
                                'news' => $news,
                                'request' => $request
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * Возвращает объект новости по переданному идентификатору
     *
     * @return News[]
     */
    private function getNews()
    {
        $params = array('campaign_id' => $this->args['campaign_id']);
        if(isset($this->args['news_id'])){
            $params['id'] = $this->args['news_id'];
        }
        return News::model()->active()->findAllByAttributes($params);
    }

    private function canPerform()
    {
        return isset($this->args['campaign_id']);
    }
}