# datatableUtils

[ ] how virtual column is rendered must be define in model via FieldRenderable interface

[ ]  Example:
            $dtC = new TableContainer( 'organizations', '\App\Models\Organization', [
                [
                    'colName'=>'guid', // colName -> column name in DB, not displayed
                    'op'=>'link_to_route',
                    'route'=>'admin.organizations.show',
                    'resourceIdCol'=>'guid', // column value to use for route param if applicable
                ],
                ['colName'=>'oname'],
                [
                    'colName'=>'number_of_projects',
                    'op'=>'virtual_column',
                ],
                [
                    'colName'=>'number_of_users',
                    'op'=>'virtual_column',
                ],
            ]);

AJAX handler:

            $records         = Organization::all();
            $records = TableContainer::renderColumnVals( $records, $request->meta);

[ ] Javascript is required! (include in library as asset!) 
