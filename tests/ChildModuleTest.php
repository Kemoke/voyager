<?php

namespace TCG\Voyager\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\FormFields\AbstractHandler;

class ChildModuleTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        $this->install();
    }

    /** @test */
    public function relationship_form_field_only_in_relationships()
    {
        // Add the new handler to Voyager...
        Voyager::addFormField(DummyHandler::class);

        $this->assertTrue(Voyager::formFieldsRelationship()->keys()->contains('dummy'));
        $this->assertFalse(Voyager::formFields()->keys()->contains('dummy'));
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
