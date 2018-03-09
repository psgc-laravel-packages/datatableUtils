<?php
namespace PsgcLaravelPackages\DatatableUtils;

class TableContainer
{

    protected $_columns;
    protected $_resourceName;

    public function __construct($resourceName)
    {
        $this->_columns = [];
        $this->_resourceName = $resourceName;
    }

    // %TODO: NOT sure this will work
    //   how will you do: $r->ownername = $r->owner->username;
    public function addColumn($_data, $_title, $_name=null)
    {
        $this->_columns[] = [
                                'data'   => $_data,
                                'title'  => $_title,
                                'name'   => !empty($_name) ? $_name : $_data;
                            ];
    }

    public function addLinkColumn($_data, $_title, $routeName, $routeArgs=null, $_name=null)
    {
        //$r->guid_link = link_to_route('site.widgets.show',$r->renderField('guid'),$r->guid)->toHtml();
                        [ "data"=>"slug_link", "title"=>"Slug", "name"=>"slug_link" ],
        $this->_columns[] = [
                                'data'   => link_to_route($routeName,$_data,
                                'title'  => $_title,
                                'name'   => !empty($_name) ? $_name : $_data;
                            ];
        // %TODO : check for implements renderField() and if it does use it here??
    }

}
