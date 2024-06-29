<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\File;

class PseudoFileModifier
{
    protected $content;

    protected $getContentPath;

    protected $searchingText = '';

    protected $matching = 0; // Change default to 0 to handle all occurrences

    protected $insertingPosition = 0;

    protected $insertingText = '';

    protected $removeStart = '';

    protected $removeEnd = '';

    protected $removeText = '';
    protected $msg;

    protected $instanceof_filemodifier;

    public function __construct($getContentPath)
    {
        if (!File::exists($getContentPath)) {
            throw new \InvalidArgumentException("Source file does not exist: $getContentPath");
        }
        $this->getContentPath = $getContentPath;
        $this->content = File::get($getContentPath);
    }

    public function instanceofFileModefier($instanceofFilemodifier)
    {
        $this->instanceof_filemodifier = $instanceofFilemodifier;

        return $this;
    }

    public static function getContent($getContentPath)
    {
        return new static($getContentPath);
    }

    public function searchingText($text, $match = 0)
    {
        return $this;
    }

    public function orSearchingText($text, $match = 0)
    {


        return $this;
    }

    public function replace()
    {
        return $this;
    }

    public function insertAfter()
    {
        return $this;
    }

    public function insertBefore()
    {
        return $this;
    }

    public function insertingText($text)
    {
        return $this;
    }

    public function delete()
    {
        return $this;
    }

    public function isExistStop()
    {
        return $this;
    }

    public function remove($startText, $endText, $text = '')
    {
        return $this;
    }

    public function save($putContentPath = null)
    {
        $this->instanceof_filemodifier->save();
        if ($this->msg) {
            echo "\n{$this->msg}";
        } else {
            echo "\nthe instance of file modefier execution";
        }
    }

    protected function applyModification()
    {
        return $this;
    }

    protected function modifySpecificOccurrence($content, $searchingText, $insertingText, $occurrence, $insertingPosition)
    {
        return $this;
    }

    protected function modifyAllOccurrences($content, $searchingText, $insertingText, $insertingPosition)
    {
        return $this;
    }



    protected function removeText($content, $startText, $endText)
    {
        return $this;
    }

    public function msg($msg = '')
    {
        $this->msg = $msg;
        return $this;
    }
    public function ifExist($msg = '')
    {
        $text = $this->instanceof_filemodifier->searchingText;
        if (strpos($this->instanceof_filemodifier->content, $text) !== false) {

            return $this->instanceof_filemodifier;
        }
        return $this;
    }

    public function ifNotExist($msg = '')
    {
        $text = $this->instanceof_filemodifier->searchingText;
        if (strpos($this->instanceof_filemodifier->content, $text) === false) {
            return $this->instanceof_filemodifier;
        }
        return $this;
    }
}
