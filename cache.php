<?php

$old = sprintf('%1.4f', memory_get_peak_usage() / 1024) .' KB';

class caches{
    public  $keep_time = 3600;
    public  $file_path = './cache.txt';
    private $handle    = null;
    private $time      = 0;
    public function __construct(){
        $this->time = time();
    }
    
    public function read($key, $keep_time=0){
        # 取得第一行. 判断过期, 不存在, 异常情况, 影响返回值即可. 返回值决定write方法的调用.
        $keep_time === 0 && $keep_time = $this->keep_time;
        $list = $this->__get_one();
        
        # 是否更新判断.
            $check = true;
        if(!$list[$key])
            $check = false;
        if($check && (time() - $list[$key]['t']) >= $keep_time)
             $check = false;
        
        # 取得第几行数据. 
        $line = $list[$key]['l']+0;
        $i = 1;
        $data = '';
        if($this->handle){
            while(!feof($this->handle)){
                if($i === $line){
                   $data = fgets($this->handle);
                }else{
                    // TODO: 有没有好办法跳过这步...
                   fgets($this->handle);
                }
                $i ++;
            }
        }
        
        # 无论如何都保障数据为数组返回.
        if($data){
            $data = $this->__data_parse($data,'DECODE');
        }
        !$data && $data = array();
        return $data;
    }
    public function write($key, $val){
        # 取得第一行. 判断key是否已经存在了..
        $list = $this->__get_one();
        
        # 什么情况下可以写入.
        if($list[$key]){
            # 已经过期的情况下. write函数被调用后, 不管如何都当作过期.
            $list[$key]['t'] = $this->time;
            $list['end'] = $list[$key]['l'];
        }else{
            $list[$key] = array('t'=>$this->time,'l'=>($list['end']+1));
            $list['end'] = $list[$key]['l'];
        }
        
        # 数据加密处理后再传给__write. $list['end'] 表示更新哪一行. 
        return $this->__write($list['end'], $this->__data_parse($val), serialize($list));
    }
    
    private function __get_one(){
        $this->__read_fopens();

        if(!$this->handle)
            return array();
        $list = array();
        if($seria = rtrim(fgets($this->handle))){
            $list = unserialize($seria);
            unset($seria);
        }
        
        !$list && $list = array();
        return $list;
    }
    
    private function __data_parse($data, $cls='ENCODE'){
        # $State 参数以减少is_string, count两函数的调用.
        if($cls === 'ENCODE'){
            # 一定要返回无换行的一行. 切记
            # gzcompress 非常占内存, 只是写入时执行.
            $data = base64_encode(gzcompress(serialize($data),9));
        }else{
            $data = unserialize(gzuncompress(base64_decode(rtrim($data))));
            if($State === false && count($data) === 1 && isset($data[0]) === true){
                $data = $data[0];
            }
        }
        return $data;
    }
    
    private function __write($line, $data, $firstline){
        $savedata = array();
        $line +=0;
        $i = 1;
        $savedata[0] = $firstline;
        if($this->handle){
            while(!feof($this->handle)){
                $savedata[$i] = rtrim(fgets($this->handle));
                if($i === $line){
                    $savedata[$i]= $data;
                }
                $i ++;
            }
        }
        
        if(!$savedata[$line])
            $savedata[$line] = $data;
        
        if($this->handle)
            $this->__closes();
            
        # 重新写入文件.
        if(!$fp = fopen($this->file_path, 'wb'))
        if(!$fp = fopen($this->file_path, 'wb'))
        if(!$fp = fopen($this->file_path, 'wb'))
        if(!$fp = fopen($this->file_path, 'wb'))
        if(!$fp = fopen($this->file_path, 'wb'))
            return 0;
        
        flock($fp, LOCK_EX | LOCK_NB);
        $ints = 0;
        //$ints = fwrite($fp, implode(PHP_EOL, $savedata));
        foreach($savedata AS $key => $val){
            if($key === 0){
                $ints += fwrite($fp,$val);
            }else{
                $ints += fwrite($fp,PHP_EOL.$val);
            }
            if($key === 0 && $ints <= 0)
                break;
        }
        unset($savedata);
        flock($fp, LOCK_UN);
        fclose($fp);
        
        return $ints;
    }
    
    private function __read_fopens(){
        $cls = 'rb';
        if($this->handle)
            $this->__closes();
        if(is_file($this->file_path) === false)
            return false;
        
       if(!$this->handle = fopen($this->file_path,$cls))
       if(!$this->handle = fopen($this->file_path,$cls))
       if(!$this->handle = fopen($this->file_path,$cls))
       if(!$this->handle = fopen($this->file_path,$cls))
       if(!$this->handle = fopen($this->file_path,$cls))
            $this->handle = null;
       if($this->handle)
       flock($this->handle,LOCK_EX | LOCK_NB);
    }
    private function __closes(){
        if($this->handle){
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
            $this->handle = null;
        }
    }
}

########################################### 调用代码 #########################################
set_time_limit(0);
$atime = microtime(true);
$obj = new caches();

$read = 1;  // (0 / 1) 写入或者读, 测试效果.
$size = 10000; // 10K
$arr = range(1,100);  // 首次要快得多, 200:1秒, 1000:11秒
foreach($arr AS $val){
    if($read == 0){
        // 写入测试.
        $ints = $obj->write('key'.$val,array('key'=>str_repeat('A',$size)));
        echo 'key'.$val.' write size: '. ($ints / 1000).' KB<br />';
    }else{
        // 读缓存测试.
        $ints = $obj->read('key'.$val);
        echo 'key'.$val.' read size: '. strlen($ints['key']) / 1000 .' KB<br />';
    }
}


#################################### 以下代码为监控作用 ####################################
echo '<br />执行时间: ';
echo sprintf('%1.4f',microtime(true) - $atime).' 秒';

echo '<br><br><br><br><hr>内存监控: ';
echo $new = sprintf('%1.4f', memory_get_peak_usage() / 1024) .' KB';
echo '<br><hr>原始内存: ';
echo $old;
echo '<br><hr>增加内存: ';
echo sprintf('%1.4f',$new - $old) .' KB';
exit();
?>