<?php

namespace TCG\Voyager\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\FormFields\AbstractHandler;
use TCG\Voyager\Models\DataRow;

class ChildModuleTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        $this->install();

        Auth::loginUsingId(1);
    }

    /** @test */
    public function relationship_form_field_only_in_relationships()
    {
        // Add the new handler to Voyager...
        Voyager::addFormField(DummyHandler::class);

        $this->assertTrue(Voyager::formFieldsRelationship()->keys()->contains('dummy'));
        $this->assertFalse(Voyager::formFields()->keys()->contains('dummy'));
    }

    /** @test */
    public function user_has_role_child_modile()
    {
        $row = DataRow::where('field', 'user_belongsto_role_relationship')->first();
        $row->details = json_encode([
            "model" => "TCG\\Voyager\\Models\\Role",
            "input_type" => "child_module",
            "table" => "roles",
            "type" => "hasOne",
            "column" => "id",
            "key" => "id",
            "label" => "name",
            "pivot_table" => "roles",
            "pivot" => "0"
        ]);
        
        $row->save();

        $this->visit(route('voyager.users.index'))->click(__('voyager.generic.add_new'))->seePageIs(route('voyager.users.create'));
        $this->assertResponseStatus(200);
        $this->see('Add User');

        $this->see('admin');
        $this->see('user');
        $this->see('Add a new role');
    }
}

class Album extends Model
{
}

class Picture extends Model
{
}

class DummyHandler extends AbstractHandler
{
    protected $codename = 'dummy';
    protected $relationshipField = true;

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return null;
    }
}
