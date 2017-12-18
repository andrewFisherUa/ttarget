<?php
class SessionsJob
{
    public function perform()
    {
        foreach(RedisSession::instance()->getLastPages() as $row){
            Sessions::model()->addPage($row[0], $row[1], $row[2]);
        }
//        foreach(RedisSession::instance()->getLastGeo() as $row){
//            Sessions::model()->addGeo($row[0], $row[1], $row[2]);
//        }
//        foreach(RedisSession::instance()->getLastTeasers() as $row){
//            Sessions::model()->addTagsByTeaserId($row[0], $row[1], $row[2]);
//        }
    }
}
