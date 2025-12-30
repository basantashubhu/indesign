<?php

namespace Santa\Indesign;

class Attribute
{
    public $attribues = [];

    public const PARAGRAPH_STYLE = 'AppliedParagraphStyle';
    public const CHARACTER_STYLE = 'AppliedCharacterStyle';
    public const UNDERLINE = 'Underline';

    public const HEAD_CATEGORY = 'ParagraphStyle/CLASSIFIEDS%3aCLASSIFIEDS_SUBHEADS%3aTOC1CATEGORY';
    public const HEAD_STATE = 'ParagraphStyle/CLASSIFIEDS%3aCLASSIFIEDS_SUBHEADS%3aTOC0STATE';
    public const HEAD_LOGO = 'ParagraphStyle/LOGO';
    public const HEAD_SUBHEAD = 'ParagraphStyle/CLASSIFIEDS%3aCLASSIFIEDS_SUBHEADS%3aHEAD';
    public const HEAD_BODY = 'ParagraphStyle/CLASSIFIEDS%3aCLASSIFIEDS_BODY%3aAD';
    public const BULLET = 'ParagraphStyle/CLASSIFIEDS%3aCLASSIFIEDS_BODY%3aBULLET';

    public const CHAR_STYLE_NONE = 'CharacterStyle/$ID/[No character style]';
    public const CHAR_STYLE_BOLD = 'CharacterStyle/GLOBAL%3aBold';
    public const CHAR_STYLE_ITALIC = 'CharacterStyle/GLOBAL%3aItalic';
    public const CHAR_STYLE_BOLD_ITALIC = 'CharacterStyle/GLOBAL%3aBold/Italic';

    public function __construct($attribues)
    {
        $this->attribues = $attribues;
    }

    public static function make(array $attributes)
    {
        return new self($attributes);
    }

    public function merge($attributes)
    {
        return new self($attributes + $this->attribues);
    }

    public function has($name)
    {
        return in_array($name, $this->attribues);
    }

    public function exists($key)
    {
        return array_key_exists($key, $this->attribues);
    }

    public function all()
    {
        return $this->attribues;
    }

    public function __toString()
    {
        $out = [];
        foreach ($this->attribues as $k => $v) {
            if ($v === null) continue;
            $out[] = $k . '="' . $v . '"';
        }
        return $out ? ' ' . implode(' ', $out) : '';
    }
}
