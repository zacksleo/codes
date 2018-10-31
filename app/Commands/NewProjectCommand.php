<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\GeneratorCommand;

class NewProjectCommand extends GeneratorCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'new:project';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = '创建新的项目';

    public function getStub()
    {
        return public_path('stubs/repo.stub');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = $this->ask('请输入项目代码：');
        $path = getcwd() . "/$id/";
        if (file_exists($path)) {
            $this->error('项目已存在');
            return false;
        }
        $name = $this->ask('请输入项目名称：');
        $template = $this->choice('请选择要使用的模板', ['yii2', 'taro', 'react-navive'], 'yii2');
        $email = $this->ask('请输入管理员邮箱：');
        $envs = [
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ];
        $cacheDir = $_SERVER['HOME'] . '/.genesis/';
        if (!file_exists($cacheDir)) {
            $this->files->makeDirectory($cacheDir);
        }
        $file = $cacheDir . '/yii2.zip';
        if (!file_exists($file)) {
            $this->comment('download template...');
            file_put_contents($file, fopen('https://codeload.github.com/zacksleo/yii2-app-advanced/zip/master', 'r'));
        }
        $zip = new \ZipArchive();
        $res = $zip->open($file);
        if ($res === true) {
            $zip->extractTo(getcwd());
            rename('yii2-app-advanced-master', $id);
            $zip->close();
            //unlink($file);
        } else {
            return $this->error("Couldn't open $file");
        }
        switch ($template) {
            case 'yii2':
                $this->buildYii($name, $envs, $path);
                break;
        }
        $this->info('created successfully.');
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @param string path
     */
    protected function buildYii($name, $envs, $path)
    {
        $this->comment('setings configs...');
        $modules = ['api', 'frontend', 'backend'];
        foreach ($modules as $module) {
            $filePath = $path . '/' . $module . '/config/main-local.php';
            $this->files->put($filePath, $this->buildMainLocal($name, $filePath));
        }
        $this->buildEnvs($envs, $path);
    }

    protected function buildEnvs($envs, $path)
    {
        $this->comment('setings envs...');
        $modules = ['', 'deploy/production/'];
        foreach ($modules as $module) {
            $filePath = $path . '/' . $module . '.env';
            $stub = $this->files->get($filePath);
            $stub = str_replace(
                ['DummyAppName', 'DummyAdminEmail', 'DummyId', 'DummyDbPassword'],
                [$envs['name'], $envs['email'], $envs['id'], str_random(12)],
                $stub
            );
            $this->files->put($filePath, $stub);
        }
    }

    protected function buildMainLocal($name, $path)
    {
        $stub = $this->files->get($path);
        $stub = str_replace(
            ['DummyValidationKey', ],
            [$this->getValidationKey($name)],
            $stub
        );
        return $stub;
    }

    protected function getValidationKey($name)
    {
        return strtoupper(md5($name));
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule) : void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
