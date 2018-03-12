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
    //   $_setter is a closure
    public function addColumn(String $_data, String $_title, Function? $_setter=null:String, String? $_name=null)
    {
        $this->_columns[] = [
                                'data'   => $_data,
                                'title'  => $_title,
                                'setter'  => $_setter, // closure
                                'name'   => !empty($_name) ? $_name : $_data;
                            ];
    }

    public function columnConfig()
    {
        $config = [ 'columns'=>[] ];
        foreach ($this->_columns as $c) {
            $config['columns'][] = [ 'data'=>$c->data, 'title'=>$c->title, 'name'=>$c->name ];
        }
        return $config;
    }

    public function renderColumnVals($records)
    {
        $columns = $this->_columns;
        $records->each(function($r) use($options) use($columns) { // Render html for each row's inline form /*
            // %TODO: patterns: linkify(), render()
            //$r->pole_id_number_link = link_to_route('site.poles.show',$r->renderField('pole_id_number'),$r->pole_id_number)->toHtml();
            //$r->crossarm_html = PcrossarmEnum::render($r->crossarm);
            //$r->pole_class_html = PclassEnum::render($r->pole_class);
            //$r->pole_condition_html = PconditionEnum::render($r->pole_condition);
            /*
            $r->ownername = $r->owner->username; // %FIXME: how to use ->renderField() here??
            $r->guid_link = link_to_route('site.widgets.show',$r->renderField('guid'),$r->guid)->toHtml();
            $r->slug_link = link_to_route('site.widgets.show',$r->renderField('slug'),$r->slug)->toHtml();
             */
            foreach ($columns as $c) {
                $cname = $c->name;
                if ( !is_null($c->setter) && is_callable($c->setter) ) {
                    $r->{$cname} = $c->setter($r); // use closure
                } // ...otherwise use 'raw' value based on cname (default, no action needed here)
            }
        });
        return $records;
    }

}
