<?php

namespace InfyOm\Generator\Commands\Publish;

use InfyOm\Generator\Utils\FileUtil;
use Symfony\Component\Console\Input\InputOption;


class GeneratorPublishCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes & init api routes, base controller, base test cases traits.';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->publishTestCases();
        $this->publishBaseController();
        $repositoryPattern = config('infyom.laravel_generator.options.repository_pattern', true);
        if ($repositoryPattern) {
            $this->publishBaseRepository();
        }
        if ($this->option('localized')) {
            $this->publishLocaleFiles();
        }
    }

    /**
     * Replaces dynamic variables of template.
     *
     * @param string $templateData
     *
     * @return string
     */
    private function fillTemplate($templateData)
    {
        $apiVersion = config('infyom.laravel_generator.api_version', 'v1');
        $apiPrefix = config('infyom.laravel_generator.api_prefix', 'api');

        $templateData = str_replace('$API_VERSION$', $apiVersion, $templateData);
        $templateData = str_replace('$API_PREFIX$', $apiPrefix, $templateData);
        $appNamespace = $this->getLaravel()->getNamespace();
        $appNamespace = substr($appNamespace, 0, strlen($appNamespace) - 1);
        $templateData = str_replace('$NAMESPACE_APP$', $appNamespace, $templateData);

        return $templateData;
    }

    private function publishTestCases()
    {
        $traitPath = __DIR__ . '/../../../templates/test/api_test_trait.stub';

        $testsPath = config('infyom.laravel_generator.path.api_test', base_path('tests/'));

        $this->publishFile($traitPath, $testsPath . 'ApiTestTrait.php', 'ApiTestTrait.php');

        if (!file_exists($testsPath . 'traits/')) {
            mkdir($testsPath . 'traits/');
            $this->info('traits directory created');
        }
    }

    private function publishBaseController()
    {
        $templateData = get_template('app_base_controller', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        $controllerPath = app_path('Http/Controllers/');

        $fileName = 'AppBaseController.php';

        if (file_exists($controllerPath . $fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        FileUtil::createFile($controllerPath, $fileName, $templateData);

        $this->info('AppBaseController created');
    }

    private function publishLocaleFiles()
    {
        $localesDir = __DIR__ . '/../../../locale/';

        $this->publishDirectory($localesDir, resource_path('lang'), 'lang', true);

        $this->comment('Locale files published');
    }

    /**
     * @param $sourceDir
     * @param $destinationDir
     * @param $dirName
     * @param bool $force
     *
     * @return bool|void
     */
    public function publishDirectory($sourceDir, $destinationDir, $dirName, $force = false)
    {
        if (file_exists($destinationDir) && !$force && !$this->confirmOverwrite($destinationDir)) {
            return;
        }

        File::makeDirectory($destinationDir, 493, true, true);
        File::copyDirectory($sourceDir, $destinationDir);

        $this->comment($dirName . ' published');
        $this->info($destinationDir);

        return true;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['fieldsFile', null, InputOption::VALUE_REQUIRED, 'Fields input as json file'],
            ['plural', null, InputOption::VALUE_REQUIRED, 'Plural Model name'],
            ['jsonFromGUI', null, InputOption::VALUE_REQUIRED, 'Direct Json string while using GUI interface'],
            ['tableName', null, InputOption::VALUE_REQUIRED, 'Table Name'],
            ['fromTable', null, InputOption::VALUE_NONE, 'Generate from existing table'],
            ['ignoreFields', null, InputOption::VALUE_REQUIRED, 'Ignore fields while generating from table'],
            ['save', null, InputOption::VALUE_NONE, 'Save model schema to file'],
            ['primary', null, InputOption::VALUE_REQUIRED, 'Custom primary key'],
            ['prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for all files'],
            ['paginate', null, InputOption::VALUE_REQUIRED, 'Pagination for index.blade.php'],
            ['skip', null, InputOption::VALUE_REQUIRED, 'Skip Specific Items to Generate (migration,model,controllers,api_controller,scaffold_controller,repository,requests,api_requests,scaffold_requests,routes,api_routes,scaffold_routes,views,tests,menu,dump-autoload)'],
            ['datatables', null, InputOption::VALUE_REQUIRED, 'Override datatables settings'],
            ['views', null, InputOption::VALUE_REQUIRED, 'Specify only the views you want generated: index,create,edit,show'],
            ['relations', null, InputOption::VALUE_NONE, 'Specify if you want to pass relationships for fields'],
            ['softDelete', null, InputOption::VALUE_NONE, 'Soft Delete Option'],
            ['media', null, InputOption::VALUE_NONE, 'Specify if model has media upload files'],
            ['forceMigrate', null, InputOption::VALUE_NONE, 'Specify if you want to run migration or not'],
            ['factory', null, InputOption::VALUE_NONE, 'To generate factory'],
            ['seeder', null, InputOption::VALUE_NONE, 'To generate seeder'],
            ['localized', null, InputOption::VALUE_NONE, 'Localize files.'],
            ['repositoryPattern', null, InputOption::VALUE_REQUIRED, 'Repository Pattern'],
            ['resources', null, InputOption::VALUE_REQUIRED, 'Resources'],
            ['connection', null, InputOption::VALUE_REQUIRED, 'Specify connection name'],
            ['migrate', null, InputOption::VALUE_NONE, 'Specify if auto migrate database or not'],

        ];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }
}
