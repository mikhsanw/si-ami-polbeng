<?php

namespace App\Services;


use Illuminate\Support\Facades\File;
use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Contracts\Foundation\Application;

class MakeViewsService
{
    use InteractsWithIO;

    public PathsAndNamespacesService $pathsAndNamespacesService;
    public function __construct(
        PathsAndNamespacesService $pathsAndNamespacesService,
        ConsoleOutput $consoleOutput,
        Application $application
    )
    {
        $this->pathsAndNamespacesService = $pathsAndNamespacesService;
        $this->output = $consoleOutput;
        $this->laravel = $application->getNamespace();
    }

    public function createDirectoryViews($namingConvention)
    {
        $directoryName = $this->pathsAndNamespacesService->getRealpathBaseCustomViews($namingConvention);
        // if the directory doesn't exist we create it
        if (!File::isDirectory($directoryName))
        {
            File::makeDirectory($directoryName, 0755, true);
            $this->line("<info>Created views directory:</info> ".$namingConvention['plural_low_name']);
        }
        else
            $this->error('Views directory '.$namingConvention['plural_low_name'].' already exists');
    }

    public function replaceContentControllerStub($namingConvention, $laravelNamespace)
    {
        $controllerStub = File::get($this->pathsAndNamespacesService->getControllerStubPath());
        $controllerStub = str_replace('DummyClass', $namingConvention['plural_name'].'Controller', $controllerStub);
        $controllerStub = str_replace('DummyModel', $namingConvention['singular_name'], $controllerStub);
        $controllerStub = str_replace('DummyVariableSing', $namingConvention['singular_low_name'], $controllerStub);
        $controllerStub = str_replace('DummyVariable', $namingConvention['plural_low_name'], $controllerStub);
        $controllerStub = str_replace('DummyNamespace', $this->pathsAndNamespacesService->getDefaultNamespaceController($laravelNamespace), $controllerStub);
        $controllerStub = str_replace('DummyRootNamespace', $laravelNamespace, $controllerStub);
        return $controllerStub;
    }

    public function findAndReplaceControllerPlaceholderColumns($columns, $controllerStub, $namingConvention)
    {
        $cols='';
        foreach ($columns as $column)
        {
            $type     = explode(':', trim($column));
            $column   = $type[0];

            // our placeholders
            $cols .= str_repeat("\t", 2).'DummyCreateVariableSing$->'.trim($column).'=$request->input(\''.trim($column).'\');'."\n";
        }

        // we replace our placeholders
        $controllerStub = str_replace('DummyUpdate', $cols, $controllerStub);
        $controllerStub = str_replace('DummyCreateVariable$', '$'.$namingConvention['plural_low_name'], $controllerStub);
        $controllerStub = str_replace('DummyCreateVariableSing$', '$'.$namingConvention['singular_low_name'], $controllerStub);

        return $controllerStub;
    }

    public function findAndReplaceIndexViewPlaceholderColumns($columns, $templateViewsDirectory, $namingConvention, $separateStyleAccordingToActions)
    {
        $thIndex=$indexView='';
        foreach ($columns as $column)
        {
            $type      = explode(':', trim($column));
            $column    = $type[0];
            if($type[1] === 'relasi'){
                $column = $type[2];
            }
            // our placeholders
            $thIndex    .=str_repeat("\t", 5).'<th class="text-center">'.ucwords(strtolower(trim($column)))."</th>\n";
        }

        $indexStub = File::get($this->pathsAndNamespacesService->getCrudgenViewsStubCustom($templateViewsDirectory).DIRECTORY_SEPARATOR.'index.stub');
        $indexStub = str_replace('DummyHeaderTable', $thIndex, $indexStub);

        return $indexStub;
    }

    public function findAndReplaceFormViewPlaceholderColumns($columns, $templateViewsDirectory, $namingConvention, $separateStyleAccordingToActions)
    {
        $formCreate='';
        foreach ($columns as $column)
        {
            $type      = explode(':', trim($column));
            $sql_type  = $type[1];
            $column    = $type[0];
            $typeHtml = $this->getHtmlType($sql_type);


            // our placeholders
            $formCreate .=str_repeat("\t", 2).'<div class="form-group">'."\n";
            if($typeHtml === 'select'){
                $formCreate .= str_repeat("\t", 3) . '{!! html()->label()->class("control-label")->for("' . trim($column) . '")->text("' . ucfirst(trim($column)) . '") !!}' . "\n";
                $formCreate .= str_repeat("\t", 3) . '{!! html()->select("' . trim($column) . '", $' . trim($column) . ', isset($data) ? $data->' . trim($column) . ' : null)->placeholder("Pilih")->class("form-control")->id("' . trim($column) . '") !!}' . "\n";
            }elseif($typeHtml === 'relasi'){
                $formCreate .= str_repeat("\t", 3) . '{!! html()->label()->class("control-label")->for("' . trim($column) . '")->text("' . ucfirst(trim($type[2])) . '") !!}' . "\n";
                $formCreate .= str_repeat("\t", 3) . '{!! html()->select("' . trim($column) . '", $' . trim($column) . ', isset($data) ? $data->' . trim($column) . ' : null)->placeholder("Pilih")->class("form-control select2")->id("' . trim($column) . '") !!}' . "\n";
            }else{
                $formCreate .= str_repeat("\t", 3) . '{!! html()->label()->class("control-label")->for("' . trim($column) . '")->text("' . ucfirst(trim($column)) . '") !!}' . "\n";
                $formCreate .= str_repeat("\t", 3) . '{!! html()->' . $typeHtml . '("' . trim($column) . '", isset($data) ? $data->' . trim($column) . ' : null)->placeholder("Type ' . ucfirst(trim($column)) . ' here")->class("form-control")->id("' . trim($column) . '") !!}' . "\n";
            }
            $formCreate .=str_repeat("\t", 2).'</div>'."\n";
        }

        $createStub = File::get($this->pathsAndNamespacesService->getCrudgenViewsStubCustom($templateViewsDirectory).DIRECTORY_SEPARATOR.'form.stub');
        $createStub = str_replace('DummyForm', $formCreate, $createStub);
        return $createStub;
    }

    public function findAndReplaceDeleteViewPlaceholderColumns($columns, $templateViewsDirectory, $namingConvention, $separateStyleAccordingToActions)
    {
        
        $deleteStub = File::get($this->pathsAndNamespacesService->getCrudgenViewsStubCustom($templateViewsDirectory).DIRECTORY_SEPARATOR.'delete.stub');

        return $deleteStub;
    }

    public function findAndReplaceShowViewPlaceholderColumns($columns, $templateViewsDirectory, $namingConvention, $separateStyleAccordingToActions)
    {
        $formEdit='';
        foreach ($columns as $column)
        {
            $type      = explode(':', trim($column));
            $sql_type  = $type[1];
            $column    = $type[0];
            // our placeholders
            $formEdit .= str_repeat("\t", 2) . '<div class="col-md-6">' . "\n";
            $formEdit .= str_repeat("\t", 3) . '<div class="form-group">' . "\n";
            $formEdit .= str_repeat("\t", 4) . '{!! html()->span()->text("' . ucfirst(trim($column)) . '")->class("control-label fw-bold") !!}' . "\n";
            $formEdit .= str_repeat("\t", 4) . '{!! html()->p("' . trim($column) . '") !!}' . "\n";
            $formEdit .= str_repeat("\t", 3) . '</div>' . "\n";
            $formEdit .= str_repeat("\t", 2) . '</div>' . "\n";
        }

        $editStub = File::get($this->pathsAndNamespacesService->getCrudgenViewsStubCustom($templateViewsDirectory).DIRECTORY_SEPARATOR.'show.stub');
        $editStub = str_replace('DummyShow', $formEdit, $editStub);
        return $editStub;
    }

    public function findAndReplaceDatatableViewPlaceholderColumns($columns, $templateViewsDirectory, $namingConvention, $separateStyleAccordingToActions)
    {
        $field='';
        foreach ($columns as $column)
        {
            $type      = explode(':', trim($column));
            $sql_type  = $type[1];
            $column    = $type[0];
            if($type[1] === 'relasi'){
                $column = $type[2].'.nama';
            }
            // our placeholders
            $field .=str_repeat("\t", 4).'{ data: \''.strtolower(trim($column)).'\' },'."\n";
        }

        $datatableStub = File::get($this->pathsAndNamespacesService->getCrudgenViewsStubCustom($templateViewsDirectory).DIRECTORY_SEPARATOR.'datatables.stub');
        $datatableStub = str_replace('Dummyfield', $field, $datatableStub);
        return $datatableStub;
    }

    public function createFileOrError($namingConvention, $contentFile, $fileName)
    {
        if(!File::exists($this->pathsAndNamespacesService->getRealpathBaseCustomViews($namingConvention).DIRECTORY_SEPARATOR.$fileName))
        {
            File::put($this->pathsAndNamespacesService->getRealpathBaseCustomViews($namingConvention).DIRECTORY_SEPARATOR.$fileName, $contentFile);
            $this->line("<info>Created View:</info> ".$fileName);
        }
        else
            $this->error('View '.$fileName.' already exists');
    }

    private function getHtmlType($sql_type)
    {
        $conversion =
        [
            'string'  => 'text',
            'text'    => 'textarea',
            'integer' => 'number',
            'date'    => 'date',
            'select'  => 'select',
            'relasi'  => 'relasi'
        ];
        return (isset($conversion[$sql_type]) ? $conversion[$sql_type] : 'text');
    }
}
