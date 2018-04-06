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
    public    $tablename;

    protected $_modelClass;
    protected $_columns;
    protected $_meta;

    // $modelClass must be fully qualified namespace
    public function __construct(string $tablename, string $modelClass, array $colConfigs)
    {
        $this->tablename = $tablename;

        // Check that this class implements FieldRenderable interface ( ~~ instanceof )
        if ( !in_array('PsgcLaravelPackages\DatatableUtils\FieldRenderable', class_implements($modelClass)) ) {
            throw new \Exception('Object must implement PsgcLaravelPackages\DatatableUtils\FieldRenderable');
        }
        $this->_modelClass = $modelClass; 

        $this->_columns = [];
        $this->_meta = [];
        foreach ($colConfigs as $ccElem) {

            $key = $ccElem['colName']; // this will *always* be set, by rule (%TODO add check)

            // %TODO: check if FieldRenderable is implemented, if not choose suitable defaults (see above)
            $c = [
                   'title'  => empty($ccElem['title']) ? $modelClass::_renderFieldKey($key) : $ccElem['title'],
                   'name'   => $key,
               ];
            $c['data'] = array_key_exists('op', $ccElem) 
                            ? $key.'_'.$ccElem['op'] // replace with string that will be used below for renderColumnVals()
                            : $key;
            $this->_columns[] = $c;

            if ( array_key_exists('op', $ccElem) ) {
                $copy = $ccElem;
                unset($copy['title']); // drop some fields
                $this->_meta[$key] = json_encode($copy);
            }
        }
    }

    public function meta() : array
    {
        return $this->_meta;
    }

    public function columns() : array
    {
        return $this->_columns;
    }


    // Set rendering for special fields such as links, FKs, etc
    //   ~ if not listed here will just default to 'pass-through' of raw column/field name's value
    //   ~ $colConfigs has to be passed by caller, as PHP doesn't carry state between requests (thus
    //        we have no way to re-init the same object of this class)
    //   %TODO: add type hints (eloquent collections? objects? array gives error)
    public static function renderColumnVals(&$records, array $meta)
    {
        //dd($meta);
        $records->each(function($r) use($meta) { // Render html for each row's inline form /*
            foreach ($meta as $ccElem) {
                $json = json_decode($ccElem);
                // %FIXME: op is required
                switch ($json->op) {
                    case 'link_to_route':
                        $resourceIdCol = $json->resourceIdCol; // slug, guid, id (pkid), etc
                        $resourceVal = $r->{$resourceIdCol}; // the actual object's value for this field
                        $renderedVal = ($r instanceof FieldRenderable) ? $r->renderField($json->colName) : $r->{$json->colName};
                        //$r->{$ccElem} =  link_to_route($json->route,$renderedVal,$resourceVal)->toHtml();
                        $r->{$json->colName.'_'.$json->op} = link_to_route($json->route,$renderedVal,$resourceVal)->toHtml();
                        //unset($meta[$ccElem]);
                        break;
                    default:
                        $r->{$ccElem} = ($r instanceof FieldRenderable) ? $r->renderField($ccElem) : $ccElem;
                }
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
