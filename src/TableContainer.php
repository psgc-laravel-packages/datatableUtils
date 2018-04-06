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

    protected $_colConfigs;
    protected $_modelClass;
    public $tablename;

    // $modelClass must be fully qualified namespace
    public function __construct(string $tablename, string $modelClass, array $colConfigs)
    {
        $this->_colConfigs = $colConfigs;
        $this->tablename = $tablename;

        // Check that this class implements FieldRenderable interface ( ~~ instanceof )
        if ( !in_array('PsgcLaravelPackages\DatatableUtils\FieldRenderable', class_implements($modelClass)) ) {
            throw new \Exception('Object must implement PsgcLaravelPackages\DatatableUtils\FieldRenderable');
        }
        $this->_modelClass = $modelClass; 
    }


    public function meta() : array
    {
        return $this->_colConfigs;
    }

    public function columns() : array
    {
        $modelClass = $this->_modelClass;

        // needed by JS at time of page render (before AJAX)
        $columns = [];
        foreach ($this->_colConfigs as $ccElem) {
            $c = [];
            if ( Helpers::isJson($cc) ) { // JSON format
                $json = json_decode($ccElem);
                switch ($json->op) {
                    case 'link_to_route':
                        // add a first
                        $c['data']  = $json->colName.'_'.$json->op; // replace with string that will be used below for renderColumnVals()
                        $c['title'] = $modelClass::_renderFieldKey($json->colName);
                        $c['name']  = $json->colName;
                        break;
                }
            } else {  // plain string format ($ccElem is same as the column data)
                $c['data']  = $ccElem;
                $c['title'] = $modelClass::_renderFieldKey($ccElem);
                $c['name']  = $ccElem;
                //$c['name'] = !empty($_name) ? $_name : $_data; // %FIXME: add _name option (?)
            }
// ---
            //$config['columns'][] = [ 'data'=>$ccElem->data, 'title'=>$ccElem->title, 'name'=>$ccElem->name ];
            $columns[] = $c;
        }
        //dump($config);
        return $columns;
    }


    // Set rendering for special fields such as links, FKs, etc
    //   ~ if not listed here will just default to 'pass-through' of raw column/field name's value
    //   ~ $colConfigs has to be passed by caller, as PHP doesn't carry state between requests (thus
    //        we have no way to re-init the same object of this class)
//   %TODO: add type hints (eloquent collections? objects? array gives error)
    public static function renderColumnVals(&$records, array $colConfigs)
    {
        $records->each(function($r) use($colConfigs) { // Render html for each row's inline form /*
            foreach ($colConfigs as $ccElem) {
                if ( Helpers::isJson($ccElem) ) {
                    $json = json_decode($ccElem);
                    switch ($json->op) {
                        case 'link_to_route':
                            $resourceIdCol = $json->resourceIdCol; // slug, guid, id (pkid), etc
                            $resourceVal = $r->{$resourceIdCol}; // the actual object's value for this field
                            $renderedVal = ($r instanceof FieldRenderable) ? $r->renderField($json->colName) : $r->{$json->colName};
                            //$r->{$ccElem} =  link_to_route($json->route,$renderedVal,$resourceVal)->toHtml();
                            $colConfigs[$json->colName.'_'.$json->op] = link_to_route($json->route,$renderedVal,$resourceVal)->toHtml();
                            unset($colConfigs[$ccElem]);
                            break;
                        default:
                            $r->{$ccElem} = ($r instanceof FieldRenderable) ? $r->renderField($ccElem) : $ccElem;
                    }
                    //dd($json);
                } else if ( is_string($ccElem) )  {
                    // ccElem is a simple string
                    $r->{$ccElem} = ($r instanceof FieldRenderable) ? $r->renderField($ccElem) : $ccElem;
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
