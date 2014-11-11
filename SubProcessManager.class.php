<?php

/**
 * 子进程管理
 * @author wclssdn <wclssdn@gmail.com>
 */
class SubProcessManager{

	/**
	 * 原始进程ID
	 * @var number
	 */
	protected $originPid;

	/**
	 * 允许递归子进程层级
	 * @var number
	 */
	protected $maxRecursion = 1;

	/**
	 * 子进程层级记录
	 * @var array
	 */
	protected $recursions = array();

	/**
	 * 子进程pid
	 * @var array
	 */
	protected $children = array();

	private function __construct($maxRecursion){
		pcntl_signal(SIGCHLD, SIG_IGN);
		$this->maxRecursion = $maxRecursion;
		$this->originPid = posix_getpid();
		// 主进程的层级为0
		$this->recursions[$this->originPid] = 0;
	}

	private function __clone(){
	}

	/**
	 * 获取子进程管理对象（单例）
	 * @param string $maxRecursion 最多的fork套嵌层数
	 * @return SubProcess
	 */
	public static function getInstance($maxRecursion = 1){
		static $instance = null;
		$instance === null && $instance = new self($maxRecursion);
		return $instance;
	}

	/**
	 * 建立子进程
	 * @param callable $callable
	 * @param array $params
	 * @throws Exception
	 * @return boolean | number 成功则返回子进程pid
	 */
	public function run($callable, array $params = array()){
		$currentPid = posix_getpid();
		if ($this->recursions[$currentPid] < $this->maxRecursion){
			$pid = pcntl_fork();
			$this->children[$currentPid][] = $pid;
			if ($pid === -1){
				throw new Exception('fork failed!');
			}elseif ($pid > 0){
				return $pid;
			}else{
				$this->recursions[posix_getpid()] = $this->recursions[$currentPid] + 1;
				$code = (int)call_user_func_array($callable, $params);
				exit($code);
			}
		}else{
			return false;
		}
	}

	/**
	 * 等待所有子进程
	 */
	public function wait(){
		$currentPid = posix_getpid();
		do{
			foreach ($this->children[$currentPid] as $index => $pid){
				$r = posix_getpgid($pid);
				if (!$r){
					unset($this->children[$currentPid][$index]);
				}
			}
			if (!$this->children[$currentPid]){
				break;
			}
		}while (true);
	}
}
