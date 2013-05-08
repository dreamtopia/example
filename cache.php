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
        # ȡ�õ�һ��. �жϹ���, ������, �쳣���, Ӱ�췵��ֵ����. ����ֵ����write�����ĵ���.
        $keep_time === 0 && $keep_time = $this->keep_time;
        $list = $this->__get_one();
        
        # �Ƿ�����ж�.
            $check = true;
        if(!$list[$key])
            $check = false;
        if($check && (time() - $list[$key]['t']) >= $keep_time)
             $check = false;
        
        # ȡ�õڼ�������. 
        $line = $list[$key]['l']+0;
        $i = 1;
        $data = '';
        if($this->handle){
            while(!feof($this->handle)){
                if($i === $line){
                   $data = fgets($this->handle);
                }else{
                    // TODO: ��û�кð취�����ⲽ...
                   fgets($this->handle);
                }
                $i ++;
            }
        }
        
        # ������ζ���������Ϊ���鷵��.
        if($data){
            $data = $this->__data_parse($data,'DECODE');
        }
        !$data && $data = array();
        return $data;
    }
    public function write($key, $val){
        # ȡ�õ�һ��. �ж�key�Ƿ��Ѿ�������..
        $list = $this->__get_one();
        
        # ʲô����¿���д��.
        if($list[$key]){
            # �Ѿ����ڵ������. write���������ú�, ������ζ���������.
            $list[$key]['t'] = $this->time;
            $list['end'] = $list[$key]['l'];
        }else{
            $list[$key] = array('t'=>$this->time,'l'=>($list['end']+1));
            $list['end'] = $list[$key]['l'];
        }
        
        # ���ݼ��ܴ�����ٴ���__write. $list['end'] ��ʾ������һ��. 
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
        # $State �����Լ���is_string, count�������ĵ���.
        if($cls === 'ENCODE'){
            # һ��Ҫ�����޻��е�һ��. �м�
            # gzcompress �ǳ�ռ�ڴ�, ֻ��д��ʱִ��.
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
            
        # ����д���ļ�.
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

########################################### ���ô��� #########################################
set_time_limit(0);
$atime = microtime(true);
$obj = new caches();

$read = 1;  // (0 / 1) д����߶�, ����Ч��.
$size = 10000; // 10K
$arr = range(1,100);  // �״�Ҫ��ö�, 200:1��, 1000:11��
foreach($arr AS $val){
    if($read == 0){
        // д�����.
        $ints = $obj->write('key'.$val,array('key'=>str_repeat('A',$size)));
        echo 'key'.$val.' write size: '. ($ints / 1000).' KB<br />';
    }else{
        // ���������.
        $ints = $obj->read('key'.$val);
        echo 'key'.$val.' read size: '. strlen($ints['key']) / 1000 .' KB<br />';
    }
}


#################################### ���´���Ϊ������� ####################################
echo '<br />ִ��ʱ��: ';
echo sprintf('%1.4f',microtime(true) - $atime).' ��';

echo '<br><br><br><br><hr>�ڴ���: ';
echo $new = sprintf('%1.4f', memory_get_peak_usage() / 1024) .' KB';
echo '<br><hr>ԭʼ�ڴ�: ';
echo $old;
echo '<br><hr>�����ڴ�: ';
echo sprintf('%1.4f',$new - $old) .' KB';
exit();
?>