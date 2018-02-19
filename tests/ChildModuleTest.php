<?php

namespace TCG\Voyager\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use TCG\Voyager\Http\Controllers\VoyagerBreadController;
use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;

class ChildModuleTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        $this->install();

        // Create the album data type...
        $albums = DataType::create([
            'name' => 'albums',
            'slug' => 'albums',
            'display_name_singular' => 'Album',
            'display_name_plural' => 'Albums',
            'icon' => 'voyager-list',
            'model_name' => 'TCG\\Voyager\\Tests\\Album',
            'policy_name' => null,
            'controller' => null,
            'description' => null,
            'generate_permissions' => 1,
            'server_side' => 0
        ]);

        Permission::generateFor('albums');

        $permissions = Permission::all();
        $role = Role::where('name', 'admin')->firstOrFail();
        $role->permissions()->sync(
            $permissions->pluck('id')->all()
        );

        // $permission = \DB::table('permissions')->where('table_name', 'albums')->get();

        // dd(\DB::table('permission_role')->get(), \DB::table('permissions')->get());

        // $dataRow = $this->dataRow($albums, 'id');
        // if (!$dataRow->exists) {
        //     $dataRow->fill([
        //         'type'         => 'number',
        //         'display_name' => 'ID',
        //         'required'     => 1,
        //         'browse'       => 0,
        //         'read'         => 0,
        //         'edit'         => 0,
        //         'add'          => 0,
        //         'delete'       => 0,
        //         'details'      => '',
        //         'order'        => 1,
        //     ])->save();
        // }
    }

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testRoles()
    {
        // Login in to the application...
        $this->visit(route('voyager.login'));
        $this->type('admin@admin.com', 'email');
        $this->type('password', 'password');
        $this->press(__('voyager.generic.login'));
        $this->seePageIs(route('voyager.dashboard'));

        // Navigate to the albums overview...
        $this->visit(route('voyager.albums.index'))
             ->click(__('voyager.generic.add_new'))
             ->seePageIs(route('voyager.albums.create'));

        // // Adding a New Role
        // $this->visit(route('voyager.roles.index'))->click(__('voyager.generic.add_new'))->seePageIs(route('voyager.roles.create'));
        // $this->type('superadmin', 'name');
        // $this->type('Super Admin', 'display_name');
        // $this->press(__('voyager.generic.submit'));
        // $this->seePageIs(route('voyager.roles.index'));
        // $this->seeInDatabase('roles', ['name' => 'superadmin']);

        // // Editing a Role
        // $this->visit(route('voyager.roles.edit', 2));
        // $this->type('regular_user', 'name');
        // $this->press(__('voyager.generic.submit'));
        // $this->seePageIs(route('voyager.roles.index'));
        // $this->seeInDatabase('roles', ['name' => 'regular_user']);

        // // Editing a Role
        // $this->visit(route('voyager.roles.edit', 2));
        // $this->type('user', 'name');
        // $this->press(__('voyager.generic.submit'));
        // $this->seePageIs(route('voyager.roles.index'));
        // $this->seeInDatabase('roles', ['name' => 'user']);

        // // Get the current super admin role
        // $superadmin_role = Role::where('name', '=', 'superadmin')->first();

        // // Deleting a Role
        // $response = $this->call('DELETE', route('voyager.roles.destroy', $superadmin_role->id), ['_token' => csrf_token()]);
        // $this->assertEquals(302, $response->getStatusCode());
        // $this->notSeeInDatabase('roles', ['name' => 'superadmin']);
    }

    protected function dataRow($type, $field)
    {
        return DataRow::firstOrNew([
            'data_type_id' => $type->id,
            'field'        => $field,
        ]);
    }
}

class Album extends Model {}
class Picture extends Model {}