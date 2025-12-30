<?php

namespace Santa\Indesign;

/**
 * Depends On:
 * @property \DOMDocument $document
 */
trait XmlHelper
{
    public function find($selector)
    {
        return $this->document->getElementsByTagName($selector);
    }

    public function findNs($selector)
    {
        return $this->document->getElementsByTagNameNS('http://ns.adobe.com/AdobeInDesign/idml/1.0/packaging', $selector);
    }

    public function insertBefore($node, $beforeNode = null)
    {
        foreach((array) $beforeNode as $bn) {
            $ref = $this->findNs($bn);
            $child = $ref->length > 0 ? $ref->item(0) : null;
            if($child === null) continue;
            $this->document->documentElement->insertBefore($node, $child);
            break;
        }
    }

    public function insertAfter($node, $afterNode = null)
    {
        foreach((array) $afterNode as $an) {
            $ref = $this->findNs($an);
            $child = $ref->length > 0 ? $ref->item(0)->nextSibling : null;
            if($child === null) continue;
            $this->document->documentElement->insertBefore($node, $child);
            break;
        }
    }

    public function __toString()
    {
        return $this->document->saveXML();
    }
}