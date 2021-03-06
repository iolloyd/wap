<?php
date_default_timezone_set('Europe/Madrid');

define('REMOTEIP'      , $_SERVER['REMOTE_ADDR']);
define('ROOT'          , dirname(dirname(__FILE__)));

////////////////////////////////////////////////////
// Global directory definitions used by framework //
////////////////////////////////////////////////////

define('CONFDIR'       , ROOT.'/app/config');

define('LIBDIR'        , ROOT.'/lib');
define('PREDISLIBDIR'  , ROOT.'/lib/predis/lib');

define('APPLIBDIR'     , ROOT.'/app/lib');
define('LOGICDIR'      , ROOT.'/app/logic');
define('TEMPLATEDIR'   , ROOT.'/app/templates');

define('LOGDIR'        , ROOT.'/logs');
define('PLUGINDIR'     , ROOT.'/plugins');

define('FORMSDIR'      , ROOT.'/app/forms/templates');

////////////////////////////////////////////////////
// Add Defined directories to php search path     //
////////////////////////////////////////////////////

set_include_path(get_include_path() . ':' . APPLIBDIR);
set_include_path(get_include_path() . ':' . LIBDIR);
set_include_path(get_include_path() . ':' . PREDISLIBDIR);
set_include_path(get_include_path() . ':' . LOGICDIR);
set_include_path(get_include_path() . ':' . PLUGINDIR);

include('utils.php');

