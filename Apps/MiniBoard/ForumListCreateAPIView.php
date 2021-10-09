<?php

namespace jeb\snahp\Apps\MiniBoard;

require_once "/var/www/forum/ext/jeb/snahp/core/Rest/Views/Generics.php";

use jeb\snahp\core\Rest\Views\ListCreateAPIView;

class ForumListCreateAPIView extends ListCreateAPIView
{
    public $serializerClass = "jeb\snahp\core\Rest\Serializers\ModelSerializer";
    protected $request;
    protected $sauth;

    public function __construct($request, $sauth, $model)
    {
        $this->request = $request;
        $this->sauth = $sauth;
        $sauth->reject_anon("Error Code: d861e86e82");
        $this->model = $model;
    }

    public function create($request)
    {
        $this->sauth->reject_non_dev("Error Code: 11e18d40f8");
        return parent::create($request);
    }
}
