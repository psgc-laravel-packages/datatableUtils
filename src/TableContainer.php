<?php
namespace PsgcLaravelPackages\DatatableUtils;

use Closure;
use stdClass;

class TableContainer
{

    protected $_columns;
    public $tablename;

    public function __construct($tablename)
    {
        $this->_columns = [];
        $this->tablename = $tablename;
    }

    // $_setter is a closure
    public function addColumn(String $_data, String $_title, Closure $_setter=null, String $_name=null)
    {
        $c = new stdClass();
        $c->data   = $_data;
        $c->title  = $_title;
        $c->setter  = $_setter; // closure
        $c->name   = !empty($_name) ? $_name : $_data;
        $this->_columns[] = $c;
    }

    public function columnConfig()
    {
        $config = [ 'columns'=>[] ];
        foreach ($this->_columns as $c) {
            $config['columns'][] = [ 'data'=>$c->data, 'title'=>$c->title, 'name'=>$c->name ];
        }
        return $config;
    }

    // Set rendering for special fields such as links, FKs, etc
    //   ~ if not listed here will just default to 'pass-through' of raw column/field name's value
    public function renderColumnVals(&$records)
    {
        $columns = $this->_columns;
        $records->each(function($r) use($columns) { // Render html for each row's inline form /*
            foreach ($columns as $c) {
                $cname = $c->name;
                if ( !is_null($c->setter) && is_callable($c->setter) ) {
                    $r->{$cname} = ($c->setter)($r); // use closure
                } // ...otherwise use 'raw' value based on cname (default, no action needed here)
            }
        });
        return $records;
    }

}


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
