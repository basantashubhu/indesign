<?php

namespace Indesign;

class Tag
{
    public $name;
    public $attributeBag;
    public $content = '';
    public $selfClosing = false;
    public $children = [];

    const INDENT = '    ';

    public function __construct($name, $attributes)
    {
        $this->name = $name;
        $this->attributeBag = Attribute::make($attributes);
    }

    public function addChild(Tag $tag)
    {
        $this->children[] = $tag;
        return $tag;
    }

    public function merge($attributes = [])
    {
        $this->attributeBag = $this->attributeBag->merge($attributes);
        return $this;
    }

    public static function Br()
    {
        return static::BreakLine();
    }

    public static function BreakLine()
    {
        $br = new self('Br', []);
        $br->selfClosing = true;
        return $br;
    }

    public static function Content($text)
    {
        $content = new self('Content', []);
        $content->content = htmlentities($text);
        return $content;
    }

    public static function CharacterStyleRange($content, $attributes = [])
    {
        $char = new self('CharacterStyleRange', [
            Attribute::CHARACTER_STYLE => Attribute::CHAR_STYLE_NONE
        ]);

        $char->merge($attributes);

        foreach ((array) $content as $child) {
            $char->addChild($child instanceof Tag ? $child : static::Content($child));
        }

        return $char;
    }

    public static function Bold($content, $attributes = [])
    {
        $attributes = $attributes + [Attribute::CHARACTER_STYLE => Attribute::CHAR_STYLE_BOLD];
        return static::CharacterStyleRange($content, $attributes);
    }

    public static function Italic($content, $attributes = [])
    {
        $attributes = $attributes + [Attribute::CHARACTER_STYLE => Attribute::CHAR_STYLE_ITALIC];
        return static::CharacterStyleRange($content, $attributes);
    }

    public static function BoldItalic($content, $attribues = [])
    {
        $attribues = $attribues + [Attribute::CHARACTER_STYLE => Attribute::CHAR_STYLE_BOLD_ITALIC];
        return static::CharacterStyleRange($content, $attribues);
    }

    public static function Underline($content, $attributes = [])
    {
        $attributes = $attributes + [Attribute::UNDERLINE => 'true'];
        return static::CharacterStyleRange($content, $attributes);
    }

    public static function ParagraphStyleRange($content, $attributes = [])
    {
        $paragraph = (new self('ParagraphStyleRange', [
            Attribute::PARAGRAPH_STYLE => Attribute::HEAD_BODY
        ]));

        $paragraph->merge($attributes);

        foreach ((array) $content as $child) {
            $paragraph->addChild($child instanceof Tag ? $child : static::CharacterStyleRange($child));
        }

        return $paragraph;
    }

    public static function Line()
    {
        return Tag::ParagraphStyleRange([[Tag::Br()]], [
            Attribute::PARAGRAPH_STYLE => 'ParagraphStyle/ADRULE'
        ]);
    }

    public function __toString()
    {
        return $this->toXml(0);
    }

    public function toXml($level = 0)
    {
        $indent = str_repeat(self::INDENT, $level);
        $attributes = (string) $this->attributeBag;

        if ($this->selfClosing || (empty($this->children) && $this->content === '')) {
            return $indent . '<' . $this->name . $attributes . ' />';
        }

        if (empty($this->children)) {
            return $indent . '<' . $this->name . $attributes . '>' . $this->content . '</' . $this->name . '>';
        }

        $lines = [];
        $lines[] = $indent . '<' . $this->name . $attributes . '>';

        foreach ($this->children as $child) {
            $lines[] = $child->toXml($level + 1);
        }

        $lines[] = $indent . '</' . $this->name . '>';

        return implode(PHP_EOL, $lines);
    }
}
