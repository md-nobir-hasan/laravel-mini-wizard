<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\File;

class FileModifier
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

    public function orSearchingText($text,$match=0)
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
        if ($this->removeStart && $this->removeEnd) {
            $this->content = $this->removeText($this->content, $this->removeStart, $this->removeEnd);
        }

        if ($this->matching > 0) {
            // Handle specific occurrence
            $this->content = $this->modifySpecificOccurrence($this->content, $this->searchingText, $this->insertingText, $this->matching, $this->insertingPosition);
        } else {
            // Handle all occurrences
            $this->content = $this->modifyAllOccurrences($this->content, $this->searchingText, $this->insertingText, $this->insertingPosition);
        }
    }

    protected function modifySpecificOccurrence($content, $searchingText, $insertingText, $occurrence, $insertingPosition)
    {
        $pattern = '/(' . preg_quote($searchingText, '/') . ')/';
        $matches = [];
        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

        if (isset($matches[0][$occurrence - 1])) {
            $match = $matches[0][$occurrence - 1];
            $position = $match[1];

            if ($insertingPosition === -1) {
                return substr_replace($content, $insertingText, $position, 0);
            } elseif ($insertingPosition === 1) {
                return substr_replace($content, $insertingText, $position + strlen($searchingText), 0);
            } else {
                return substr_replace($content, $insertingText, $position, strlen($searchingText));
            }
        }

        return $content;
    }

    protected function modifyAllOccurrences($content, $searchingText, $insertingText, $insertingPosition)
    {
        $pattern = '/(' . preg_quote($searchingText, '/') . ')/';

        if ($insertingPosition === -1) {
            return preg_replace($pattern, $insertingText . '$1', $content);
        } elseif ($insertingPosition === 1) {
            return preg_replace($pattern, '$1' . $insertingText, $content);
        } else {
            return preg_replace($pattern, $insertingText, $content);
        }
    }

    protected function removeText($content, $startText, $endText)
    {
        $pattern = '/' . preg_quote($startText, '/') . '.*?' . preg_quote($endText, '/') . '/s';
        return preg_replace($pattern, '', $content);
    }

    public function ifExist($msg=''){
        $text = $this->searchingText;
        if (strpos($this->content, $text) !== false) {
            return $this;
        }
        $pseudo_modifier = PseudoFileModifier::getContent($this->getContentPath)->searchingText($this->searchingText)->msg($msg);
        return $pseudo_modifier;
    }

    public function ifNotExist($msg = '')
    {
        $text = $this->searchingText;
        if (strpos($this->content, $text) === false) {
            return $this;
        }
        $pseudo_modifier = PseudoFileModifier::getContent($this->getContentPath)->searchingText($this->searchingText)->msg($msg);
        return $pseudo_modifier;
    }
}
