<?php

namespace Billing\Commands;
// $editor = new FileEditor('file.php');
// $editor->appendAfterWord('world', 'kdksdfklsdflsdfkl');
class FileEditor
{
    private $filename;
    private $file;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function __destruct()
    {
        if ($this->file) {
            $this->closeFile();
        }
    }

    public function openFile()
    {
        $this->file = fopen($this->filename, 'w');
    }

    public function writeToFile($text)
    {
        fwrite($this->file, $text);
    }

    public function closeFile()
    {
        fclose($this->file);
    }

    public function appendAfterWord($word, $text)
    {
        $contents = file_get_contents($this->filename);
        $position = strpos($contents, $word);
        if ($position !== false) {
            $start = substr($contents, 0, $position + strlen($word));
            $end = substr($contents, $position + strlen($word));
            file_put_contents($this->filename, $start . "\n" . $text . "\n" . $end);
        }
    }
}
