<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\File;

class FileModifier
{
    protected $content;

    protected $getContentPath;

    protected $searchingText = '';

    protected $matching = 1;

    protected $insertingPosition = 0;

    protected $insertingText = '';

    protected $removeStart = '';

    protected $removeEnd = '';

    protected $removeText = '';

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

    public function searchingText($text, $match = 1)
    {
        $this->searchingText = $text;
        $this->matching = $match;

        return $this;
    }

    public function orSearchingText($text)
    {
        $text = $this->searchingText;
        if (strpos($this->content, $text) == false) {
            $this->searchingText = $text;
        }

        return $this;
    }

    public function replace()
    {
        $this->insertingPosition = 0;

        return $this;
    }

    public function insertAfter()
    {
        $this->insertingPosition = 1;

        return $this;
    }

    public function insertBefore()
    {
        $this->insertingPosition = -1;

        return $this;
    }

    public function insertingText($text)
    {
        $this->insertingText = $text;
        $this->applyModification();

        return $this;
    }

    public function delete()
    {
        $this->insertingText = '';
        $this->insertingPosition = 0;
        $this->applyModification();

        return $this;
    }

    public function isExistStop()
    {
        $text = $this->insertingText;
        if (strpos($this->content, $text) !== false) {
            throw new \Exception("Text '$text' already exists in the content.");
        }

        return $this;
    }

    public function isExist($text)
    {
        if (strpos($this->content, $text) !== false) {
            return true;
        }

        return false;
    }



    public function remove($startText, $endText, $text = '')
    {
        $this->removeStart = $startText;
        $this->removeEnd = $endText;
        $this->removeText = $text;

        return $this;
    }

    public function save($putContentPath = null)
    {
        if (!$putContentPath) {
            $putContentPath = $this->getContentPath;
        }
        File::put($putContentPath, $this->content);

        return true;
    }

    protected function applyModification()
    {
        // Search for the target text
        $position = $this->strposNth($this->content, $this->searchingText, $this->matching);

        // If the searching text is not found, position at the end of the content
        if ($position === false) {
            $position = strlen($this->content);
            $this->insertingPosition = 1;
        }

        // Handle removing text if specified
        if ($this->removeStart && $this->removeEnd) {
            $this->content = $this->removeText($this->content, $this->removeStart, $this->removeEnd);
            $position = strpos($this->content, $this->removeStart);
            if ($position === false) {
                $position = strlen($this->content);
                $this->insertingPosition = 1;
            }
            $this->insertingText = $this->removeText;
        }

        // Insert or replace text
        $this->content = $this->insertOrReplaceText($this->content, $position, $this->searchingText, $this->insertingText, $this->insertingPosition);
    }

    protected function strposNth($haystack, $needle, $n)
    {
        $offset = 0;
        for ($i = 1; $i <= $n; $i++) {
            $pos = strpos($haystack, $needle, $offset);
            if ($pos === false) {
                return false;
            }
            $offset = $pos + 1;
        }

        return $pos;
    }

    protected function removeText($content, $startText, $endText)
    {
        $removeStart = strpos($content, $startText);
        $removeEnd = strpos($content, $endText, $removeStart) + strlen($endText);

        if ($removeStart !== false && $removeEnd !== false) {
            return substr_replace($content, '', $removeStart, $removeEnd - $removeStart);
        }

        return $content;
    }

    protected function insertOrReplaceText($content, $position, $searchingText, $insertingText, $insertingPosition)
    {
        if ($insertingPosition === -1) {
            // Insert before the searching text
            return substr_replace($content, $insertingText, $position, 0);
        } elseif ($insertingPosition === 1) {
            // Insert after the searching text
            return substr_replace($content, $insertingText, $position + strlen($searchingText), 0);
        } else {
            // Replace the searching text
            return substr_replace($content, $insertingText, $position, strlen($searchingText));
        }
    }
}
