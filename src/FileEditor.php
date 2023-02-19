<?php

namespace Wemx\Installer;

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
        if (file_exists($this->filename)) {
            $contents = file_get_contents($this->filename);
            $position = strpos($contents, $word);
            if ($position !== false) {
                $start = substr($contents, 0, $position + strlen($word));
                $end = substr($contents, $position + strlen($word));
                file_put_contents($this->filename, $start . "\n" . $text . "\n" . $end);
            }
        }

    }

    public function issetText($text)
    {
        $contents = file_get_contents($this->filename);
        return strpos($contents, $text) !== false;
    }

    public static function appendAfter($file, $word, $text)
    {
        if (file_exists($file)) {
            $contents = file_get_contents($file);
            $isset = strpos($contents, $text) !== false;
            if (!$isset) {
                $position = strpos($contents, $word);
                if ($position !== false) {
                    $start = substr($contents, 0, $position + strlen($word));
                    $end = substr($contents, $position + strlen($word));
                    file_put_contents($file, $start . "\n" . $text . "\n" . $end);
                }
            }
        }
    }

    public static function appendBefore($file, $word, $text)
    {
        if (file_exists($file)) {
            $contents = file_get_contents($file);
            $isset = strpos($contents, $text) !== false;
            if (!$isset) {
                $position = strpos($contents, $word);
                if ($position !== false) {
                    $newContents = substr_replace($contents, "\n" . $text . "\n", $position, 0);
                    file_put_contents($file, $newContents);
                }
            }
        }
    }
}
