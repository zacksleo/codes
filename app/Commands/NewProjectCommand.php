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
    protected $description = 'New Project';

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
        $id = $this->ask('Code');
        $path = getcwd() . "/$id/";
        if (file_exists($path)) {
            $this->error('Project Exists');
            return false;
        }
        $name = $this->ask('Name');
        $template = $this->choice('Template', ['yii2'], 'yii2');
        $email = $this->ask('Admin Email');
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
                $this->buildEnvs($envs, $path);
                break;
        }
        $this->info('created successfully.');
    }

    protected function buildEnvs($envs, $path)
    {
        $this->comment('setings envs...');
        $modules = ['', 'deploy/production/'];
        foreach ($modules as $module) {
            $filePath = $path . '/' . $module . '.env';
            $stub = $this->files->get($filePath);
            $stub = str_replace(
                ['DummyAppName', 'DummyAdminEmail', 'DummyId', 'DummyDbPassword', 'DummyValidationKey'],
                [$envs['name'], $envs['email'], $envs['id'], str_random(12), strtoupper(md5($envs['name']))],
                $stub
            );
            $this->files->put($filePath, $stub);
        }
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
