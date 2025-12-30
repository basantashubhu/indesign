<?php

namespace Indesign;

use Closure;
use DOMDocument;
use DOMElement;
use DOMException;
use Exception;

class ArrayToXml
{
    /**
     * @var DOMDocument
     */
    protected $document;

    /**
     * @var DOMElement
     */
    protected $rootNode;

    /**
     * @var bool
     */
    protected $replaceSpacesByUnderScoresInKeyNames = true;

    /**
     * @var bool
     */
    protected $addXmlDeclaration = true;

    /**
     * @var string
     */
    protected $numericTagNamePrefix = 'numeric_';

    /**
     * @var array
     */
    protected $options = array(
        'convertNullToXsiNil' => false,
        'convertBoolToString' => false,
    );

    /**
     * @param array $array
     * @param string|array $rootElement
     * @param bool $replaceSpacesByUnderScoresInKeyNames
     * @param string|null $xmlEncoding
     * @param string $xmlVersion
     * @param array $domProperties
     * @param bool|null $xmlStandalone
     * @param bool $addXmlDeclaration
     * @param array|null $options
     */
    public function __construct(
        array $array,
        $rootElement = '',
        $replaceSpacesByUnderScoresInKeyNames = true,
        $xmlEncoding = null,
        $xmlVersion = '1.0',
        array $domProperties = array(),
        $xmlStandalone = null,
        $addXmlDeclaration = true,
        $options = array('convertNullToXsiNil' => false, 'convertBoolToString' => false)
    ) {
        $encoding = ($xmlEncoding === null) ? '' : $xmlEncoding;
        $this->document = new DOMDocument($xmlVersion, $encoding);

        if (! is_null($xmlStandalone)) {
            $this->document->xmlStandalone = $xmlStandalone;
        }

        if (! empty($domProperties)) {
            $this->setDomProperties($domProperties);
        }

        $this->addXmlDeclaration = (bool) $addXmlDeclaration;

        if (! is_array($options)) {
            $options = array();
        }

        $this->options = array_merge($this->options, $options);

        $this->replaceSpacesByUnderScoresInKeyNames = (bool) $replaceSpacesByUnderScoresInKeyNames;

        if (! empty($array) && $this->isArrayAllKeySequential($array)) {
            throw new DOMException('Invalid Character Error');
        }

        $this->rootNode = $this->createRootElement($rootElement);

        $this->document->appendChild($this->rootNode);

        $this->convertElement($this->rootNode, $array);
    }

    /**
     * @param string $prefix
     * @return void
     */
    public function setNumericTagNamePrefix($prefix)
    {
        $this->numericTagNamePrefix = $prefix;
    }

    /**
     * @param array $array
     * @param string|array $rootElement
     * @param bool $replaceSpacesByUnderScoresInKeyNames
     * @param string|null $xmlEncoding
     * @param string $xmlVersion
     * @param array $domProperties
     * @param bool|null $xmlStandalone
     * @param bool $addXmlDeclaration
     * @param array $options
     * @return string
     */
    public static function convert(
        array $array,
        $rootElement = '',
        $addXmlDeclaration = true,
        $prettyPrint = false,
        $xmlEncoding = null,
        $xmlVersion = '1.0',
        array $domProperties = array(),
        $xmlStandalone = null,
        $replaceSpacesByUnderScoresInKeyNames = true,
        array $options = array('convertNullToXsiNil' => false)
    ) {
        $converter = new static(
            $array,
            $rootElement,
            $replaceSpacesByUnderScoresInKeyNames,
            $xmlEncoding,
            $xmlVersion,
            $domProperties,
            $xmlStandalone,
            $addXmlDeclaration,
            $options
        );

        if($prettyPrint) {
            $converter->prettify();
        }

        return $converter->toXml();
    }

    /**
     * @param int $options
     * @return string
     */
    public function toXml($options = 0)
    {
        if ($this->addXmlDeclaration) {
            return $this->document->saveXML(null, $options);
        }

        return $this->document->saveXML($this->document->documentElement, $options);
    }

    /**
     * @return DOMDocument
     */
    public function toDom()
    {
        return $this->document;
    }

    /**
     * @param array $domProperties
     * @return void
     * @throws Exception
     */
    protected function ensureValidDomProperties(array $domProperties)
    {
        foreach ($domProperties as $key => $value) {
            if (! property_exists($this->document, $key)) {
                throw new Exception("{$key} is not a valid property of DOMDocument");
            }
        }
    }

    /**
     * @param array $domProperties
     * @return $this
     * @throws Exception
     */
    public function setDomProperties(array $domProperties)
    {
        $this->ensureValidDomProperties($domProperties);

        foreach ($domProperties as $key => $value) {
            $this->document->{$key} = $value;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function prettify()
    {
        $this->document->preserveWhiteSpace = false;
        $this->document->formatOutput = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function dropXmlDeclaration()
    {
        $this->addXmlDeclaration = false;

        return $this;
    }

    /**
     * @param string $target
     * @param string $data
     * @return $this
     */
    public function addProcessingInstruction($target, $data)
    {
        $elements = $this->document->getElementsByTagName('*');

        $rootElement = ($elements->length > 0) ? $elements->item(0) : null;

        $processingInstruction = $this->document->createProcessingInstruction($target, $data);

        $this->document->insertBefore($processingInstruction, $rootElement);

        return $this;
    }

    /**
     * @param DOMElement $element
     * @param mixed $value
     * @return void
     */
    protected function convertElement(DOMElement $element, $value)
    {
        if ($value instanceof Closure) {
            $value = $value();
        }

        $sequential = $this->isArrayAllKeySequential($value);

        if (! is_array($value)) {
            $value = htmlspecialchars($value === null ? '' : $value);

            $value = $this->removeControlCharacters($value);

            $element->nodeValue = $value;

            return;
        }

        foreach ($value as $key => $data) {
            if (! $sequential) {
                if (($key === '_attributes') || ($key === '@attributes')) {
                    $this->addAttributes($element, $data);
                } elseif ((($key === '_value') || ($key === '@value')) && is_scalar($data)) {
                    $element->nodeValue = htmlspecialchars($data);
                } elseif ((($key === '_cdata') || ($key === '@cdata')) && is_scalar($data)) {
                    $element->appendChild($this->document->createCDATASection($data));
                } elseif ((($key === '_mixed') || ($key === '@mixed')) && is_scalar($data)) {
                    $fragment = $this->document->createDocumentFragment();
                    $fragment->appendXML($data);
                    $element->appendChild($fragment);
                } elseif ($key === '__numeric') {
                    $this->addNumericNode($element, $data);
                } elseif (strpos($key, '__custom:') === 0) {
                    $parts = preg_split('/(?<!\\\):/', $key);
                    $customKey = isset($parts[1]) ? $parts[1] : '';
                    $this->addNode($element, str_replace('\:', ':', $customKey), $data);
                } elseif (strpos($key, '_comment') === 0 || strpos($key, '@comment') === 0) {
                    if ($data !== null && $data !== '') {
                        $element->appendChild(new \DOMComment($data));
                    }
                } else {
                    $this->addNode($element, $key, $data);
                }
            } elseif (is_array($data)) {
                $this->addCollectionNode($element, $data);
            } else {
                $this->addSequentialNode($element, $data);
            }
        }
    }

    /**
     * @param DOMElement $element
     * @param mixed $value
     * @return void
     */
    protected function addNumericNode(DOMElement $element, $value)
    {
        foreach ($value as $key => $item) {
            $this->convertElement($element, array($this->numericTagNamePrefix . $key => $item));
        }
    }

    /**
     * @param DOMElement $element
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function addNode(DOMElement $element, $key, $value)
    {
        if ($this->replaceSpacesByUnderScoresInKeyNames) {
            $key = str_replace(' ', '_', $key);
        }

        $child = $this->document->createElement($key);

        $this->addNodeTypeAttribute($child, $value);

        $element->appendChild($child);

        $value = $this->convertNodeValue($child, $value);

        $this->convertElement($child, $value);
    }

    /**
     * @param DOMElement $element
     * @param mixed $value
     * @return mixed
     */
    protected function convertNodeValue(DOMElement $element, $value)
    {
        if ($this->options['convertBoolToString'] && is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        return $value;
    }

    /**
     * @param DOMElement $element
     * @param mixed $value
     * @return void
     */
    protected function addNodeTypeAttribute(DOMElement $element, $value)
    {
        if ($this->options['convertNullToXsiNil'] && is_null($value)) {
            if (! $this->rootNode->hasAttribute('xmlns:xsi')) {
                $this->rootNode->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            }

            $element->setAttribute('xsi:nil', 'true');
        }
    }

    /**
     * @param DOMElement $element
     * @param mixed $value
     * @return void
     */
    protected function addCollectionNode(DOMElement $element, $value)
    {
        if ($element->childNodes->length === 0 && $element->attributes->length === 0) {
            $this->convertElement($element, $value);

            return;
        }

        $child = $this->document->createElement($element->tagName);
        $element->parentNode->appendChild($child);
        $this->convertElement($child, $value);
    }

    /**
     * @param DOMElement $element
     * @param mixed $value
     * @return void
     */
    protected function addSequentialNode(DOMElement $element, $value)
    {
        if (empty($element->nodeValue) && ! is_numeric($element->nodeValue)) {
            $element->nodeValue = htmlspecialchars($value);

            return;
        }

        $child = $this->document->createElement($element->tagName);
        $child->nodeValue = htmlspecialchars($value);
        $element->parentNode->appendChild($child);
    }

    /**
     * @param array|string|null $value
     * @return bool
     */
    protected function isArrayAllKeySequential($value)
    {
        if (! is_array($value)) {
            return false;
        }

        if (count($value) <= 0) {
            return true;
        }

        if (key($value) === '__numeric') {
            return false;
        }

        return array_unique(array_map('is_int', array_keys($value))) === array(true);
    }

    /**
     * @param DOMElement $element
     * @param array $data
     * @return void
     */
    protected function addAttributes(DOMElement $element, array $data)
    {
        foreach ($data as $attrKey => $attrVal) {
            $element->setAttribute($attrKey, $attrVal === null ? '' : $attrVal);
        }
    }

    /**
     * @param string|array $rootElement
     * @return DOMElement
     */
    protected function createRootElement($rootElement)
    {
        if (is_string($rootElement)) {
            $rootElementName = $rootElement ? $rootElement : 'root';

            return $this->document->createElement($rootElementName);
        }

        $rootElementName = isset($rootElement['rootElementName']) ? $rootElement['rootElementName'] : 'root';

        $element = $this->document->createElement($rootElementName);

        foreach ($rootElement as $key => $value) {
            if ($key !== '_attributes' && $key !== '@attributes') {
                continue;
            }

            $this->addAttributes($element, $value);
        }

        return $element;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function removeControlCharacters($value)
    {
        return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
    }
}
