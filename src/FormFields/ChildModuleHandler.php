<?php

namespace TCG\Voyager\FormFields;

class ChildModuleHandler extends AbstractHandler
{
    protected $codename = 'child_module';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('voyager::formfields.child_module', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }
}
