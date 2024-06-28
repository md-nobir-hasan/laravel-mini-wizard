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
    public function __construct($getContentPath)
    {
        if (!File::exists($getContentPath)) {
            throw new \InvalidArgumentException("Source file does not exist: $getContentPath");
        }
        $this->getContentPath = $getContentPath;
        $this->content = File::get($getContentPath);
    }

    public static function getContent($getContentPath)
    {
        return new static($getContentPath);
    }

    public function searchingText($text, $match = 0)
    {
        $this->searchingText = $text;
        $this->matching = $match;

        return $this;
    }

    public function orSearchingText($text, $match = 0)
    {
        $previous_searching_text = $this->searchingText;
        if (strpos($this->content, $previous_searching_text) === false) {
            $this->searchingText = $text;
            $this->matching = $match;
        }

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
        if($this->msg){
            echo "\n{$this->msg}";
        }else{
            echo "\nPseudo file modifier execution";
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
        $text = $this->searchingText;
        if (strpos($this->content, $text) !== false) {
            $real_file_modifier = FileModifier::getContent($this->getContentPath)->searchingText($this->searchingText);
            return $real_file_modifier;

        }
        return $this;
    }

    public function ifNotExist($msg = '')
    {
        $text = $this->searchingText;
        if (strpos($this->content, $text) === false) {
            $real_file_modifier = FileModifier::getContent($this->getContentPath)->searchingText($this->searchingText);
            return $real_file_modifier;

        }
        return $this;
    }
}
