<?php
namespace Common;

use Phalcon\Cli\Task;
use Phalcon\Config;
use PhalconHelpers\Filesystem;

class BaseTask extends Task
{

    protected function createPid($taskName = null, $actionName = null)
    {
        $taskName = is_null($taskName) ? $this->dispatcher->getTaskName() : $taskName;
        $actionName = is_null($actionName) ? $this->dispatcher->getActionName() : $actionName;
        $taName = strtolower("{$taskName}_{$actionName}");
        $pid = self::getPid($taskName, $actionName);
        $pidFile = $this->config->task->$taName->pid_file;
        if ($pid == - 2) {
            exit("Please open the posix extension ! \n");
        } else if ($pid == - 3) {
            exit("The $pidFile cannot be read ! \n");
        } else if ($pid != - 1) {
            exit("The $taName process already exists ! PID is $pid .\n");
        }
        $pid = posix_getpid();
        $path = dirname($pidFile);
        if (! is_dir($path)) {
            Filesystem::mkdir($path) || exit("Failed to create directory $path !");
        }
        $filePutRes = file_put_contents($pidFile, $pid);
        $filePutRes || exit("The $pidFile writing failed ! \n");
        return $pid;
    }

    protected function getPid($taskName, $actionName)
    {
        if (! extension_loaded('posix')) {
            return - 2;
        }
        $pidFile = self::getPidFile($taskName, $actionName);
        if (! is_file($pidFile)) {
            return - 1;
        }
        if (! is_readable($pidFile)) {
            return - 3;
        }
        $pid = file_get_contents($pidFile);
        $gid = posix_getpgid($pid);
        if ($gid === false) {
            return - 1;
        }
        return $pid;
    }

    protected function getPidFile($taskName, $actionName)
    {
        $taName = strtolower("{$taskName}_{$actionName}");
        $taskConfig = $this->config->task;
        if (! isset($taskConfig->$taName)) {
            $taskConfig->$taName = new Config();
        }
        if (! isset($taskConfig->$taName->pid_file)) {
            $taskConfig->$taName->pid_file = new Config();
        }
        if (! $taskConfig->$taName->pid_file) {
            $taskConfig->$taName->pid_file = $this->config->application->default_pid_path . $taName . '.pid';
        }
        return $taskConfig->$taName->pid_file;
    }
}