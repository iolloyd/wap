<?php
class user{
    public static function getSessionId(){
        if (!empty($_GET['pre_session_id'])) {
            session_id($_GET['pre_session_id']);
        }
        $sid = session_id();
        if (empty($sid)) {
            session_start();
            $sid = session_id();
        }
        return $sid;
    }

    /**
     * Stores data in the session space for current user.
     */
    public static function save($data){
        $session_id = self::getSessionId();
        $r = new dbredis();
        foreach ($data as $k => $v) {
            $r->hmset('session:'.$session_id, $k, $v); 
        }
    }

    public static function get($key){
        $r = new dbredis();
        return $r->hget('session:'.$session_id, $k);
    }

    public static function getSession(){
        $r = new dbredis();
        $session_id = self::getSessionId();
        return $r->hgetall('session:'.$session_id);
    }
}
