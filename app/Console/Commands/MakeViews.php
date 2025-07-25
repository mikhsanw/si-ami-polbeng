<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\MakeGlobalService;
use App\Services\MakeViewsService;
use Illuminate\Support\Facades\File;
use App\Services\PathsAndNamespacesService;

class MakeViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:views {directory} {columns}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make views';

    public MakeViewsService $makeViewsService;
    public MakeGlobalService $makeGlobalService;
    public PathsAndNamespacesService $pathsAndNamespacesService;

    public function __construct(
        MakeViewsService $makeViewsService,
        MakeGlobalService $makeGlobalService,
        PathsAndNamespacesService $pathsAndNamespacesService
    )
    {
        parent::__construct();
        $this->makeViewsService = $makeViewsService;
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
        $templateViewsDirectory = 'views';
        $separateStyleAccordingToActions = [
            'index'=>
            [
                'extends'=>'layouts.backend.index',
                'section'=>'content'
            ],
            'show'=>
            [
                'extends'=>'layouts.backend.index',
                'section'=>'content'
            ],
        ];

        if(!File::isDirectory($this->pathsAndNamespacesService->getCrudgenViewsStubCustom($templateViewsDirectory)))
        {
            if($templateViewsDirectory=='views')
                $this->error("Publish the default theme with: php artisan vendor:publish --provider=\"Mrdebug\Crudgen\CrudgenServiceProvider\" or create your own default-theme directory here: ".$this->pathsAndNamespacesService->getCrudgenViewsStub());
            else
                $this->error("Do you have created a directory called ".$templateViewsDirectory." here: ".$this->pathsAndNamespacesService->getCrudgenViewsStub().'?');
            return;
        }
        else
        {
            $stubs=['index', 'form', 'delete', 'datatables', 'show'];
            // check if all stubs exist
            foreach ($stubs as $stub)
            {
                if (!File::exists($this->pathsAndNamespacesService->getCrudgenViewsStubCustom($templateViewsDirectory).DIRECTORY_SEPARATOR.$stub.'.stub'))
                {
                    $this->error('Please create this file: '.$this->pathsAndNamespacesService->getCrudgenViewsStubCustom($templateViewsDirectory).DIRECTORY_SEPARATOR.$stub.'.stub');
                    return;
                }
            }
        }

        // we create our variables to respect the naming conventions
        $directoryName    = $this->argument('directory');
        $namingConvention = $this->makeGlobalService->getNamingConvention($directoryName);

        $columns = $this->argument('columns');
        // if the columns argument is empty, we create an empty array else we explode on the comma
        $columns = ($columns=='') ? [] : explode(',', $columns);

        /* *************************************************************************

                                        VIEWS

        ************************************************************************* */

        // if the directory doesn't exist we create it
        $this->makeViewsService->createDirectoryViews($namingConvention);


        /* ************************** index view *************************** */

        $contentIndex = $this->makeViewsService->findAndReplaceIndexViewPlaceholderColumns($columns, $templateViewsDirectory, $namingConvention, $separateStyleAccordingToActions);
        $this->makeViewsService->createFileOrError($namingConvention, $contentIndex, 'index.blade.php');


        /* ************************** Form view *************************** */

        $contentForm = $this->makeViewsService->findAndReplaceFormViewPlaceholderColumns($columns, $templateViewsDirectory, $namingConvention, $separateStyleAccordingToActions);
        $this->makeViewsService->createFileOrError($namingConvention, $contentForm, 'form.blade.php');

        /* ************************** Delete view *************************** */

        $contentDelete = $this->makeViewsService->findAndReplaceDeleteViewPlaceholderColumns($columns, $templateViewsDirectory, $namingConvention, $separateStyleAccordingToActions);
        $this->makeViewsService->createFileOrError($namingConvention, $contentDelete, 'delete.blade.php');

        /* ************************** datatable view *************************** */

        $contentDatatable = $this->makeViewsService->findAndReplaceDatatableViewPlaceholderColumns($columns, $templateViewsDirectory, $namingConvention, $separateStyleAccordingToActions);
        $this->makeViewsService->createFileOrError($namingConvention, $contentDatatable, 'datatables.blade.php');

        /* ************************** Show view *************************** */

        $contentShow = $this->makeViewsService->findAndReplaceShowViewPlaceholderColumns($columns, $templateViewsDirectory, $namingConvention, $separateStyleAccordingToActions);
        $this->makeViewsService->createFileOrError($namingConvention, $contentShow, 'show.blade.php');
    }
}
