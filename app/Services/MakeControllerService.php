<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Contracts\Foundation\Application;

class MakeControllerService
{
    use InteractsWithIO;

    public PathsAndNamespacesService $pathsAndNamespacesService;
    public MakeGlobalService $makeGlobalService;
    public function __construct(
        PathsAndNamespacesService $pathsAndNamespacesService,
        ConsoleOutput $consoleOutput,
        Application $application,
        MakeGlobalService $makeGlobalService
    )
    {
        $this->pathsAndNamespacesService = $pathsAndNamespacesService;
        $this->output = $consoleOutput;
        $this->laravel = $application->getNamespace();
        $this->makeGlobalService = $makeGlobalService;
    }

    public function replaceContentControllerStub($namingConvention, $laravelNamespace)
    {
        $controllerStub = File::get($this->pathsAndNamespacesService->getControllerStubPath());
        $controllerStub = str_replace('DummyClass', $namingConvention['plural_name'].'Controller', $controllerStub);
        return $controllerStub;
    }

    public function findAndReplaceControllerPlaceholderColumns($columns, $controllerStub, $namingConvention)
    {
        $cols='';
        $Select='';
        $relasiloop=1;
        $relasi='';
        foreach ($columns as $column)
        {
            $type     = explode(':', trim($column));
            $sqlType = $type[1];
            $column   = $type[0];
            // our placeholders
            $cols .= str_repeat("\t", 5)."'".trim($column)."' => 'required|'.config('master.regex.json'),\n";

            if($sqlType === 'relasi'){
                $modelname = $this->makeGlobalService->getNamingConvention($type[2]);
                $Select .= str_repeat("\t", 3)."'".trim($column)."'".str_repeat("\t", 1)."=> \App\Models\\".$modelname['singular_name']."::pluck('nama','id'),\n";
                if(($relasiloop == 1)){
                    $relasi .= "'".$modelname['singular_low_name']."'";
                }else{
                    $relasi .= ",'".$modelname['singular_low_name']."'";
                }
                $relasiloop++;
            }elseif($sqlType === 'select'){
                $Select .= str_repeat("\t", 3)."'".trim($column)."'".str_repeat("\t", 1)."=> config('master.".trim($column)."'),\n";
            }
        }
        $cols = $this->makeGlobalService->cleanLastLineBreak($cols);
        
        $SelectCreate = '';
        $datacreate = '';

        $querydata = 'query()';
        if($Select !== ''){
            $SelectCreate .= str_repeat("\t", 2) . '$data=[' . "\n";
            $SelectCreate .= $Select;
            $SelectCreate .= str_repeat("\t", 2)."];\n";
            $datacreate = ',$data';
            
            if($relasi !== ''){
                $querydata = 'with('.$relasi.')->get()';
            }
        }
        // we replace our placeholders
        $controllerStub = str_replace('DummyQuery', $querydata, $controllerStub);
        $controllerStub = str_replace('DummySelectEdit', $Select, $controllerStub);
        $controllerStub = str_replace('DummySelectCreate', $SelectCreate, $controllerStub);
        $controllerStub = str_replace('DummyDataCreate', $datacreate, $controllerStub);
        $controllerStub = str_replace('DummyValidation', $cols, $controllerStub);

        return $controllerStub;
    }

    public function createControllerFile($pathNewController, $controllerStub, $namingConvention)
    {
        if(!File::exists($pathNewController))
        {
            File::put($pathNewController, $controllerStub);
            $this->line("<info>Created Controller:</info> ".$namingConvention['plural_name']);
            $this->info("Don't forget to add routes (in web.php) like this : Route::resource('".$namingConvention['plural_low_name']."', ".$namingConvention['plural_name']."Controller::class);");
        }
        else
            $this->error('Controller '.$namingConvention['plural_name'].' already exists');
    }

    public function makeCompleteControllerFile($namingConvention, $columns, $laravelNamespace)
    {
        $controllerStub = $this->replaceContentControllerStub($namingConvention, $laravelNamespace);
        $controllerStub = $this->findAndReplaceControllerPlaceholderColumns($columns, $controllerStub, $namingConvention);

        // if our controller doesn't exists we create it
        $pathNewController = $this->pathsAndNamespacesService->getRealpathBaseCustomController($namingConvention);
        $this->createControllerFile($pathNewController, $controllerStub, $namingConvention);
    }
}
