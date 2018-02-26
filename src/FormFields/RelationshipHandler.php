<?php

namespace TCG\Voyager\FormFields;

class RelationshipHandler extends AbstractHandler
{
    protected $codename = 'default_relationship_field';
    protected $relationshipField = true;

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return null;
    }
}
