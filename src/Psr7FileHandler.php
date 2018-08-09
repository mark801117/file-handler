<?php
namespace Cloud\Filehandler;

use Cloud\StringHelper\StringHelper;
use Cloud\Filehandler\Exceptions\FileHandlerException;
/**
 * Description of Psr7FileHandler
 *
 * @author Cloud
 */
class Psr7FileHandler 
{
    protected $file;
    protected $new_file_name;
    public function __construct(\Psr\Http\Message\UploadedFileInterface $file = null) 
    {
        $this->file=$file;
    }
    public function getFileName()
    {
        return $this->new_file_name;
    }
    public function getSubName()
    {
        return end(explode('.', $this->file->getClientFilename()));
    }    
    public function getFileSize()
    {
        return $this->file->getSize();
    }
    public function generateUniqueRandomFileName()
    {
        $SH=new StringHelper();
        $name=$SH->getUniqueString().".".$this->getSubName();
        $this->new_file_name=$name;
        return $name;
    }
    public function removeDir($dir_path, $rm_self=true)
    {
        $dh=opendir($dir_path);
        if ($dh!==false) {
            while (false !==($item=readdir($dh))) {
                if ($item=='.' || $item=='..') {
                    continue;
                }
                $f="{$dir_path}/{$item}";
                if (is_dir($f)) {
                    $this->removeDir($f, true);
                } else {
                    $this->deleteFile($f);
                }
            }
            closedir($dh);
            if ($rm_self) {
                if(!rmdir($dir_path)) {
                    throw new FileHandlerException("can't rmdir {$dir_path}");
                }
            }
        } else {
            throw new FileHandlerException("can't open {$dir_path}");
        }
    }    
    public function deleteFile($file)
    {
        if (is_file($file) && file_exists($file)) {
            if (!unlink($file)) {
                throw new \Exception("Cant delete file: {$file}");
            }
        } else {
            throw new \Exception("{$file} is not a file or not exist");
        }
    }
    public function upload($dest_directory, $mkdir=true, $new_file_name=false)
    {
        $dest_file=false;
        if (empty($this->file)) {
            throw new \Exception("No file can upload");
        }
        if ($this->file->getError()!==UPLOAD_ERR_OK) {
            throw new \Exception("Error File");
        }
        if ($new_file_name===false) {
            $this->generateUniqueRandomFileName();
            $new_file_name=$this->new_file_name;
        }
        if (!is_dir($dest_directory)) {
            if($mkdir) {
                if(!mkdir($dest_directory, 0777, true)) {
                    throw new FileHandlerException("mkdir fail : {$dest_directory}");
                }
            } else {
                throw new FileHandlerException("{$dest_directory} not exists");
            }
        }
        $dest_file=$dest_directory."/{$new_file_name}";
        $this->new_file_name=$new_file_name;
        $this->file->moveTo($dest_file);
    }
}
