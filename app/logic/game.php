<?php
class game extends controller {
    var $layout = 'game';

    public function login($request) {
        $this->template('game/login');
    }

    public function loginPost($request) {
        $template = (form::isValid($_POST)) ? 'play' : 'login'; 
        $pin      = $_POST['pin'];
        if (!($phone = $this->r->get('pin:phone:'.$pin))) {
            $questions = config::read('questions', 'questions');
            $this->redirect('/game/play', array(
                'questions' => $questions,
                'phone'     => 625242747//$phone
            ));
        } else {
            $this->template('game/login');
        }
    }

    public function play(){
        $time = microtime(true);
        $this->template('game/play', array(
            'time'      => $time,
            'questions' => config::read('questions', 'questions')
        ));
    }

    public function playPost($request) {
        $questions = config::read('questions', 'questions');
        $time      = microtime(true) - $_POST['time'];
        $correct   = array_map(
            function($x) {return $x['answer'];}, 
            config::read('questions', 'questions')
        );
        $answers   = @$_POST['answers'] ? $_POST['answers'] : array();
        $good = array_filter( $answers, function($x) use ($correct) {
                if (in_array($x, $correct)) return $x; }
        );

        $this->template('game/finished', array(
            'number_correct' => count($good),
            'time_taken'     => round($time, 0)
        ));
    }

}


