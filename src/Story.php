<?php

namespace Indesign;

class Story
{
    public $id;
    public $content = [];

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function addContent(Tag $tag)
    {
        $this->content[] = $tag;
        return $this;
    }

    public function __toString()
    {
        $lines = [];
        foreach ($this->content as $item) {
            $lines[] = $item->toXml(2);
        }

        $content = implode(PHP_EOL, $lines);
        $indent = Tag::INDENT;
        $storyIndent = $indent;
        $childIndent = $indent . $indent;

        $output = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . PHP_EOL;
        $output .= '<idPkg:Story xmlns:idPkg="http://ns.adobe.com/AdobeInDesign/idml/1.0/packaging" DOMVersion="21.0">' . PHP_EOL;
        $output .= $storyIndent . '<Story Self="' . $this->id . '" UserText="true" IsEndnoteStory="false" AppliedTOCStyle="n" TrackChanges="false" StoryTitle="$ID/" AppliedNamedGrid="n">' . PHP_EOL;
        $output .= $childIndent . '<StoryPreference OpticalMarginAlignment="false" OpticalMarginSize="12" FrameType="TextFrameType" StoryOrientation="Horizontal" StoryDirection="LeftToRightDirection" />' . PHP_EOL;
        $output .= $childIndent . '<InCopyExportOption IncludeGraphicProxies="true" IncludeAllResources="false" />' . PHP_EOL;

        if ($content !== '') {
            $output .= $content . PHP_EOL;
        }

        $output .= $storyIndent . '</Story>' . PHP_EOL;
        $output .= '</idPkg:Story>';

        return $output;
    }

    public function save()
    {
        file_put_contents(storage_path("dtxapi/data/idml/Stories/Story_$this->id.xml"), (string) $this);
    }
}
