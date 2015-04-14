<?php
class controller extends mixin
{
    var $layout = 'main';
    public function __construct($redisConnection){
        parent::__construct();
        $this->r = $redisConnection();
    }

    public function __call($name, $args){
        //echo 'You called ' . CONTROLLER.'::'.METHOD.  ' ?';
    }

    public function call($uri, $args){
        $uri = ltrim(trim($uri), '/');
        list($controller, $method) = explode('/', $uri);
        call_user_func_array([new $controller(), $method], [$args]); 
    }

    public function redirect($uri){
        header("Location: $uri");
    }

    /**
     * Provides a choice of layout dependant on 
     * available routes
     */
    protected function loadAB($routes, $layout, $templateName){
        if (!in_array($templateName, array_keys($routes))){
            return array(
                'layout' => $layout,
                'template' => $templateName 
            );
        }

        $choices = [];
        foreach ($routes as $url => $options) {
            foreach ($options as $opt) {
                for($x=0;$x < $opt['weight'];$x++){
                    $choices[] = $opt;
                }
            }
        }

        $choice   = mt_rand(0, count($choices) - 1);
        $choice   = $choices[$choice];

        return [
            'layout'   => $choice['layout'],
            'template' => $choice['template'],
        ];
    }

    protected function template($templateName, $vars=array()){
        $d = debug_backtrace();
        $o = new $d[1]['object'];
        foreach ($vars as $k => $v) {
            $$k = $v;
        }
        $layout   = $o->layout;
        $ab       = $this->loadAB($layout, $templateName);
        $layout   = $ab['layout'];
        $templateName = $ab['template'];
        $wrapper  = TEMPLATEDIR.'/'.$layout.'.php';
        $content  = TEMPLATEDIR.'/'.$templateName.'.php';

        ob_start();
        require $content;
        $content = ob_get_contents();
        ob_get_clean();

        if (file_exists($wrapper)) {
            require($wrapper);
        }
    }
}
