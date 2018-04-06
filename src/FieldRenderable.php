<?php
namespace PsgcLaravelPackages\DatatableUtils;

// How a DB field is rendered
interface FieldRenderable {

    public static function _renderFieldKey(string $key) : string;
    public function renderFieldKey(string $key) : string;
    public function renderField(string $field) : ?string;

}
