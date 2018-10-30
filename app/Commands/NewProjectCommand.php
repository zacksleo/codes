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
    protected $signature = 'new-project';

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
        $name = $this->ask('请输入项目名称：');
        $template = $this->choice('请选择要使用的模板', ['yii2', 'taro', 'react-navive'], 'yii2');

        $path = base_path() . "/$name/";

        if (file_exists($path)) {
            $this->error('项目已存在');
            return false;
        }
        $this->files->copyDirectory(public_path('stubs/' . $template . '/'), $path);
        //var_dump($this->files->get($this->getStub($template)));

        //$this->files->put($path, $this->buildClass($name));

        $this->info(' created successfully.');

    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
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
