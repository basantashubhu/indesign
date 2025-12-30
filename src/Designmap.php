<?php

namespace Santa\Indesign;

class Designmap
{
    use XmlHelper;

    /**
     * @var \DOMDocument
     */
    public $document;
    public $file = 'designmap.xml';
    public static $cwd = 'dtxapi/data/idml';

    public function __construct($file)
    {
        $this->document = new \DOMDocument('1.0', 'UTF-8');
        $this->document->standalone = true;
        $this->document->load($file);
    }

    public static function get()
    {
        return new self(storage_path(static::$cwd . "/designmap.xml"));
    }

    public function addSpread(Spread $spread)
    {
        $element = $this->document->createElement('idPkg:Spread');
        $element->setAttribute('src', "Spreads/$spread->file");
        $this->document->documentElement->insertBefore($element, $this->find('Section')->item(0));
        $spread->save();
    }

    public function addLinks()
    {
        $linkElement = $this->document->createElement('idPkg:Links');
        $linkElement->setAttribute('src', "Resources/Links.xml");
        $this->insertBefore($linkElement, 'Fonts');
    }

    public function clear()
    {
        // clear spreads
        foreach($this->findNs('Spread') as $spread) {
            $spread->parentNode->removeChild($spread);
            $file = storage_path(static::$cwd . '/' . $spread->getAttribute('src'));
            file_exists($file) && unlink($file);
        }
    }

    public function save()
    {
        $this->document->preserveWhiteSpace = false;
        $this->document->formatOutput = true;
        $this->document->save(storage_path(static::$cwd . "/designmap.xml"));
    }
}