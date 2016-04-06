SubProcessManger
================

A subprocess manager in PHP

## usage
<pre>
&lt;?php

require 'SubProcessManager.php';
//允许子进程拥有子进程
$sub = SubProcessManager::getInstance(2);

echo 'i\'m main process ', posix_getpid(), ' parent is ', posix_getppid(), PHP_EOL;

$sub->run(function() use($sub){
        echo 'i\'m a sub process xxx ', posix_getpid(), ' parent is ', posix_getppid(), PHP_EOL;
        $sub->run(function(){
                sleep(3);
                echo 'i\'m a sub sub process ', posix_getpid(), ' parent is ', posix_getppid(), PHP_EOL;
        });
        $sub->wait();
});

$sub->run(function (){
        echo 'i\'m a sub process yyy ', posix_getpid(), ' parent is ', posix_getppid(), PHP_EOL;
});

echo 'main begin wait ',posix_getpid(), PHP_EOL;
$sub->wait();
echo 'main wait done ', posix_getpid(), PHP_EOL;
</pre>
