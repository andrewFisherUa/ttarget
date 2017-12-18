<?php

class ServiceCommand extends CConsoleCommand
{
    public function actionCampaignTeasers($id, $encrypted=true, $separator = "\|")
    {
        $campaign = Campaigns::model()->with(array(
            'news' => array(
                'together' => false,
                'with' => array(
                    'teasers' => array(
                        'together' => false,
                    )
                )
            )
        ))->findByPk($id);
        $teasers = array();
        foreach ($campaign->news as $news) {
            foreach ($news->teasers as $teaser) {
                $teasers[] = $encrypted ? $teaser->getEncryptedLink() : $teaser->id;
            }
        }
        echo implode($separator, $teasers) . "\n";
    }

    public function actionSegmentsPath()
    {
        foreach(Segments::model()->findAll('`parent_id` IS NULL') as $segment){
            $this->_updateSegments($segment);
        }
    }

    function _updateSegments(Segments $segment)
    {
        $segment->save(false);
        foreach($segment->segments as $next){
            $this->_updateSegments($next);
        }
    }
}