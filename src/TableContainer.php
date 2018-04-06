<?php
namespace PsgcLaravelPackages\DatatableUtils;

use Closure;
use stdClass;

use PsgcLaravelPackages\Utils\Helpers; // %FIXME: outside dependency
use PsgcLaravelPackages\DatatableUtils\FieldRenderable;


// %FIXME: this package requires that models implement Nameable interface (which btw is local to project, should be
// moved to be part of this package)
class TableContainer
{

    protected $_columns;
    protected $_modelClass;
    public $tablename;

    // $modelClass must be fully qualified namespace
    public function __construct(string $tablename, string $modelClass=null, array $cols=null)
    {
        $this->_columns = [];
        $this->tablename = $tablename;

        // legacy...%TODO eventually remove the condition and always do this, and change addColumn() to protected
        if ( !is_null($modelClass) && !empty($cols) ) {
            $this->_modelClass = $modelClass;
            $this->_addColumns( $cols );
        }

    }

    // %FIXME: really need to get package versioning
    public function initLegacy(string $tablename) 
    {
        return new self($tablename);
    }

    // %TODO: put in package, call in constructor!
    // $modelClass must be fully qualified namespace
    protected function _addColumns(array $cols)
    {
        $modelClass = $this->_modelClass;

        // Check that this class implements FieldRenderable interface ( ~~ instanceof )
        if ( !in_array('PsgcLaravelPackages\DatatableUtils\FieldRenderable', class_implements($modelClass)) ) {
            throw new \Exception('Object must implement PsgcLaravelPackages\DatatableUtils\FieldRenderable');
        }

        foreach ($cols as $col) {
            $this->addColumn(
                $col, // prefix with underscore
                //'_'.$col, // prefix with underscore %TODO: do this in the pakcage lib , renderColumnVals()
                $modelClass::_renderFieldKey($col)
            );
        }
    }

    public function addColumn(string $_data, string $_title, string $_name=null)
    {
        if ( Helpers::isJson($_data) ) {
            $json = json_decode($_data);
            switch ($json->op) {
                case 'link_to_route':
                    $_data = $json->colName.'_'.$json->op; // replace with string that will be used below for renderColumnVals()
                    break;
            }
        }

        $c = new stdClass();
        $c->data   = $_data;
        $c->title  = $_title;
        $c->name   = !empty($_name) ? $_name : $_data;
        $this->_columns[] = $c;
    }

    public function columnConfig() : array
    {
        // needed by JS at time of page render (before AJAX)
        $config = [ 'columns'=>[] ];
        foreach ($this->_columns as $c) {
            $config['columns'][] = [ 'data'=>$c->data, 'title'=>$c->title, 'name'=>$c->name ];
        }
        return $config;
    }

    // Set rendering for special fields such as links, FKs, etc
    //   ~ if not listed here will just default to 'pass-through' of raw column/field name's value
//   %TODO: add type hints (eloquent collections? objects? array gives error)
    public static function renderColumnVals(&$records, array $columns)
    {
        //$columns = $this->_columns;
        $records->each(function($r) use($columns) { // Render html for each row's inline form /*
            foreach ($columns as $colElem) {
                if ( Helpers::isJson($colElem) ) {
                    $json = json_decode($colElem);
                    switch ($json->op) {
                        case 'link_to_route':
                            $resourceIdCol = $json->resourceIdCol; // slug, guid, id (pkid), etc
                            $resourceVal = $r->{$resourceIdCol}; // the actual object's value for this field
                            $renderedVal = ($r instanceof FieldRenderable) ? $r->renderField($json->colName) : $r->{$json->colName};
                            //$r->{$colElem} =  link_to_route($json->route,$renderedVal,$resourceVal)->toHtml();
                            $columns[$json->colName.'_'.$json->op] = link_to_route($json->route,$renderedVal,$resourceVal)->toHtml();
                            unset($columns[$colElem]);
                            break;
                        default:
                            $r->{$colElem} = ($r instanceof FieldRenderable) ? $r->renderField($colElem) : $colElem;
                    }
                    //dd($json);
                } else if ( is_string($colElem) )  {
                    // colElem is a simple string
                    $r->{$colElem} = ($r instanceof FieldRenderable) ? $r->renderField($colElem) : $colElem;
                } else {
                    throw new \Exception('Column Object must be json string or simple string');
                }
                /*
                if ( !is_null($c->setter) && is_callable($c->setter) ) {
                    $r->{$cname} = ($c->setter)($r); // use closure
                } // ...otherwise use 'raw' value based on cname (default, no action needed here)
                 */
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
