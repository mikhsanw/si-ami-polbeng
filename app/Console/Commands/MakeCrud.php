<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Services\MakeControllerService;
use App\Services\MakeGlobalService;
use App\Services\MakeMigrationService;
use App\Services\MakeModelService;
use App\Services\MakeRequestService;
use App\Services\PathsAndNamespacesService;

class MakeCrud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud {crud_name} {columns}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a CRUD';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public MakeControllerService $makeControllerService;
    public MakeRequestService $makeRequestService;
    public MakeMigrationService $makeMigrationService;
    public MakeModelService $makeModelService;
    public MakeGlobalService $makeGlobalService;
    public PathsAndNamespacesService $pathsAndNamespacesService;

    public function __construct(
        MakeControllerService $makeControllerService,
        MakeRequestService $makeRequestService,
        MakeMigrationService $makeMigrationService,
        MakeModelService $makeModelService,
        MakeGlobalService $makeGlobalService,
        PathsAndNamespacesService $pathsAndNamespacesService
    )
    {
        parent::__construct();
        $this->makeControllerService = $makeControllerService;
        $this->makeRequestService = $makeRequestService;
        $this->makeMigrationService = $makeMigrationService;
        $this->makeModelService = $makeModelService;
        $this->makeGlobalService = $makeGlobalService;
        $this->pathsAndNamespacesService = $pathsAndNamespacesService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // we create our variables to respect the naming conventions
        $crudName         = ucfirst($this->argument('crud_name'));
        $namingConvention = $this->makeGlobalService->getNamingConvention($crudName);
        $columns          = $this->makeGlobalService->parseColumns($this->argument('columns'));
        $laravelNamespace = $this->laravel->getNamespace();
        
        /* *************************************************************************

                                     CONTROLLER

        ************************************************************************* */

        $this->makeControllerService->makeCompleteControllerFile($namingConvention, $columns, $laravelNamespace);

        /* *************************************************************************

                                        VIEWS

        ************************************************************************* */

        $this->call
        (
            'make:views',
            [
                'directory'=> $crudName,
                'columns'=> $this->argument('columns')
            ]
        );

        /* *************************************************************************

                                        REQUEST

        ************************************************************************* */

        // $this->makeRequestService->makeCompleteRequestFile($namingConvention, $columns, $laravelNamespace);

        /* *************************************************************************

                                        MODEL

        ************************************************************************* */

        if(!File::exists($this->pathsAndNamespacesService->getRealpathBaseModel()))
            File::makeDirectory($this->pathsAndNamespacesService->getRealpathBaseModel());

        // we create our model
        $this->createRelationships([], $namingConvention, $columns);


        /* *************************************************************************

                                        MIGRATION

        ************************************************************************* */

        $this->makeMigrationService->makeCompleteMigrationFile($namingConvention, $columns);

        /* *************************************************************************

                                        ROUTE

        ************************************************************************* */
        // $route = "\nRoute::resource('".$namingConvention['plural_low_name']."', \App\Http\Controllers".DIRECTORY_SEPARATOR.$namingConvention['plural_name']."Controller::class);";

        $route ='';
        $route .=str_repeat("\t", 1).'Route::prefix(\''.$namingConvention['plural_low_name'].'\')->as(\''.$namingConvention['plural_low_name'].'\')->group(function () {'."\n";
        $route .=str_repeat("\t", 2).'Route::get(\'/delete/{id}\', \''.$namingConvention['plural_name'].'Controller@delete\');'."\n";
        $route .=str_repeat("\t", 1).'});'."\n";
        $route .=str_repeat("\t", 1).'Route::resource(\''.$namingConvention['plural_low_name'].'\', \''.$namingConvention['plural_name'].'Controller\');'."\n\n";
        $route .=str_repeat("\t", 0).'//gencrud';

        $file = file_get_contents(base_path('routes/backend.php'));
        $search = "//gencrud";
        $replace = $route;
        $filereplace = str_replace($search, $replace, $file);
        file_put_contents(base_path('routes/backend.php'), $filereplace);

        $this->line("<info>Created Route:</info> ".$namingConvention['plural_low_name']);


        $this->line("<info>DONE</info> (NB :Generate CRUD ini sifatnya hanya membantu, cek kembali seluruh filenya)");

    }

    private function createRelationships($infos, $namingConvention, $columns)
    {
        $singularName = $namingConvention['singular_name'];
        if ($this->confirm('Do you want to create relationships between this model and an other one?'))
        {
            $type = $this->choice(
                'Which type?',
                ['belongsTo', 'hasOne', 'hasMany', 'belongsToMany', 'Cancel']
            );

            //if cancel choice is selected, we make a basic model
            if($type=="Cancel")
                // $this->call('make:model', ['name' => $this->pathsAndNamespacesService->getDefaultNamespaceCustomModel($this->laravel->getNamespace(), $singularName)]);
                $this->makeModelService->makeModelFile($infos, $singularName, $columns, $namingConvention, $this->laravel->getNamespace());
            //we want a name for this model
            else
                $this->setNameModelRelationship($type, $namingConvention, $infos, $columns);
        }
        //we don't confirm, 2 cases
        else
        {
            //$infos is empty we didn't really create a relationship
            if(empty($infos))
                // $this->call('make:model', ['name' => $this->pathsAndNamespacesService->getDefaultNamespaceCustomModel($this->laravel->getNamespace(), $singularName)]);
                $this->makeModelService->makeModelFile($infos, $singularName, $columns, $namingConvention, $this->laravel->getNamespace());
            //we get all relationships asked and we create our model
            else
                $this->makeModelService->makeCompleteModelFile($infos, $singularName, $columns, $namingConvention, $this->laravel->getNamespace());
        }
    }

    private function setNameModelRelationship($type, $namingConvention, $infos, $columns)
    {
        $nameOtherModel = $this->ask('What is the name of the other model? ex:Post');

        //we stock all relationships in $infos
        $correctNameOtherModel = ucfirst($nameOtherModel);
        $correctNameOtherModelWithNamespace = $this->laravel->getNamespace().'Models\\'.$correctNameOtherModel;
        if($this->confirm('Do you confirm the creation of this relationship? "'.'$this->'.$type.'(\''.$correctNameOtherModelWithNamespace .'\')"'))
        {
            $infos[]= ['name'=>$nameOtherModel, 'type'=>$type];
            $this->createRelationships($infos, $namingConvention, $columns);
        }
        else
            $this->setNameModelRelationship($type, $namingConvention, $infos, $columns);
    }
}
