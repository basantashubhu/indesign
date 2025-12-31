<?php

namespace Santa\Indesign;

class IdmlAction
{
    public function __invoke($json, $spreadCnt)
    {
        $design = Designmap::get();
        $design->clear();
        for($i = 0; $i < $spreadCnt; $i++) {
            $design->addSpread(Spread::make(2));
        }
        $design->addLinks();
        $design->save();

        $story = new Story('u2b8');

        // dd($json);

        foreach ($json as $order) {

            $story->addContent(Tag::Line());

            if ("$order->index0") {
                $story->addContent(Tag::ParagraphStyleRange(["$order->index0", Tag::Br()], [
                    Attribute::PARAGRAPH_STYLE => Attribute::HEAD_CATEGORY
                ]));
            }

            if ("$order->index1") {
                $story->addContent(Tag::ParagraphStyleRange(["$order->index1", Tag::Br()], [
                    Attribute::PARAGRAPH_STYLE => Attribute::HEAD_STATE
                ]));
            }

            if ("$order->ctext") {
                $this->setText(rtrim($order->ctext).PHP_EOL, $story, $order);
            }
        }

        $story->save();
    }

    private function tags()
    {
        return [
            'PIC_START' => 'PIC_END',
            'strong' => '/strong',
            'BOLD-ON' => 'BOLD-OFF',
            'B' => '/B',
            'em' => '/em',
            'ITALICS_ON' => 'ITALICS_OFF',
            'I' => '/I',
            'u' => '/u',
            'UNDER_ON' => 'UNDER_OFF',
            'U' => '/U',
        ];
    }

    private function prepareReplace($text, &$replaces = [])
    {
        $clean = true;
        foreach ($this->tags() as $key => $close) {
            if (str_contains($text, "<$key>") || str_contains($text, "[$key]")) {

                preg_match_all(
                    // "#\[$key\]([^\[$close\]]*)\[$close\]#", 
                    '#' . preg_quote("[$key]", '#') .
                    '((?:(?!' . preg_quote("[$close]", '#') . ').)*)' .
                    preg_quote("[$close]", '#') . '#',
                    $text,
                    $matches
                );

                foreach ($matches[0] as $i => $pattern) {
                    $id = uniqid();
                    $content = $this->prepareReplace($matches[1][$i], $replaces);
                    $replaces[] = [
                        'ID' => $id,
                        'TAG' => $key,
                        'CONTENT' => $content
                    ];
                    $text = str_replace($pattern, "<>$id<>", $text);
                    $clean = false;
                }

                preg_match_all(
                    // "#<$key>([^<$close>]*)<$close>#", 
                    '#' . preg_quote("<$key>", '#') .
                    '((?:(?!' . preg_quote("<$close>", '#') . ').)*)' .
                    preg_quote("<$close>", '#') . '#',
                    $text,
                    $matches1
                );

                foreach ($matches1[0] as $i => $pattern) {
                    $id = uniqid();
                    $content = $this->prepareReplace($matches1[1][$i], $replaces);
                    $replaces[] = [
                        'ID' => $id,
                        'TAG' => $key,
                        'CONTENT' => $content
                    ];
                    $text = str_replace($pattern, "<>$id<>", $text);
                    $clean = false;
                }
            }
        }

        if ($clean) {
            return $text;
        }

        return $this->prepareReplace($text, $replaces);
    }

    private function getContens($text, $replaces, $order)
    {
        $contents = [];
        foreach (explode('<>', $text) as $line) {
            if ($line === '') {
                continue;
            }
            $dynamic = false;
            foreach ($replaces as $replace) {
                if ($line === $replace['ID']) {
                    $dynamic = $replace;
                    break;
                }
            }
            $content = [];
            if ($dynamic) {
                $goods = $this->getContens($dynamic['CONTENT'], $replaces, $order);
                $goods = empty($goods) ? [$dynamic['CONTENT']] : $goods;
                foreach ($goods as $good) {
                    if (is_array($good)) {
                        foreach ($good as $g) {
                            if ($g instanceof Tag) {
                                if ($g->attributeBag->exists(Attribute::UNDERLINE)) {
                                    if (in_array($dynamic['TAG'], ['strong', 'BOLD-ON', 'B'])) {
                                        $g->merge([
                                            Attribute::CHARACTER_STYLE => Attribute::CHAR_STYLE_BOLD
                                        ]);
                                    } elseif (in_array($dynamic['TAG'], ['em', 'ITALICS_ON', 'I'])) {
                                        $g->merge([
                                            Attribute::CHARACTER_STYLE => Attribute::CHAR_STYLE_ITALIC
                                        ]);
                                    }
                                } elseif ($g->attributeBag->exists(Attribute::CHARACTER_STYLE)) {
                                    if (in_array($dynamic['TAG'], ['u', 'UNDER_ON', 'U'])) {
                                        $g->merge([
                                            Attribute::UNDERLINE => 'true'
                                        ]);
                                    } else {
                                        $g->merge([
                                            Attribute::CHARACTER_STYLE => Attribute::CHAR_STYLE_BOLD_ITALIC
                                        ]);
                                    }
                                }
                            }
                            $content[] = $g;
                        }
                        continue;
                    }
                    if (in_array($dynamic['TAG'], ['strong', 'BOLD-ON', 'B'])) {
                        $content[] = Tag::Bold($good);
                    } elseif (in_array($dynamic['TAG'], ['em', 'ITALICS_ON', 'I'])) {
                        $content[] = Tag::Italic($good);
                    } elseif (in_array($dynamic['TAG'], ['u', 'UNDER_ON', 'U'])) {
                        $content[] = Tag::Underline($good);
                    } elseif (in_array($dynamic['TAG'], ['br', 'BR'])) {
                        $content[] = [Tag::Br()];
                    } elseif (in_array($dynamic['TAG'], ['PIC_START'])) {
                        foreach(range(1,9) as $i) {
                            $pic = $order->{"picture$i"};
                            if("$pic") {
                                $content[] = new Image("u32$i", "$pic", $i);
                                unset($order->{"picture$i"});
                                break;
                            }
                        }
                    }
                }
            } else {
                $content = $line;
            }

            if ($content) {
                $contents[] = $content;
            }
        }
        return $contents;
    }

    private function setText($ctext, Story $story, $order)
    {
        $replaces = [];
        $text = str_replace(PHP_EOL, "<br />", $ctext);
        $text = preg_replace("#\{picture[1-9]\}#", "[PIC_START][PIC_END]", $text);
        $text = $this->prepareReplace($text, $replaces);

        preg_match_all("#(<br />)#", $text, $brs);
        
        foreach ($brs[0] as $i => $pattern) {
            $id = uniqid();
            $body = $brs[1][$i];
            $replaces[] = [
                'ID' => $id,
                'TAG' => 'BR',
                'CONTENT' => $body
            ];
            $text = str_replace($pattern, "<>$id<>", $text);
        }

        $contents = $this->getContens($text, $replaces, $order);

        foreach ($contents as $content) {
            if($content instanceof Tag && $content->name === 'ParagraphStyleRange') {
                $story->addContent($content);
                continue;
            }
            $story->addContent(Tag::ParagraphStyleRange($content));
        }

        // dd($ctext, json_encode($replaces, JSON_PRETTY_PRINT), explode('<>', $text), $order, "$story");
    }
}
