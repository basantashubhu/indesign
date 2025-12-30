<?php

namespace Santa\Indesign;

class Image extends Tag
{
    public $id;
    public $url;
    public $index;

    public function __construct($id, $url, $index)
    {
        $this->id = $id;
        $this->url = $url;
        $this->index = $index;

        parent::__construct('CharacterStyleRange', [
            Attribute::CHARACTER_STYLE => Attribute::CHAR_STYLE_NONE
        ]);

        $this->content = ArrayToXml::convert([
            '@attributes' => [
                'Self' => "{$id}r",
            ],
            'Properties' => [
                'PathGeometry' => [
                    'GeometryPathType' => [
                        '@attributes' => [
                            'PathOpen' => 'false'
                        ],
                        'PathPointArray' => [
                            'PathPointType' => [
                                [
                                    '@attributes' => [
                                        'Anchor' => '50.34042553191477 -172.89957446808523',
                                        'LeftDirection' => '50.34042553191477 -172.89957446808523',
                                        'RightDirection' => '50.34042553191477 -172.89957446808523'
                                    ],
                                ],
                                [
                                    '@attributes' => [
                                        'Anchor' => '50.34042553191477 -49.580425531914784',
                                        'LeftDirection' => '50.34042553191477 -49.580425531914784',
                                        'RightDirection' => '50.34042553191477 -49.580425531914784'
                                    ],
                                ],
                                [
                                    '@attributes' => [
                                        'Anchor' => '173.65957446808522 -49.580425531914784',
                                        'LeftDirection' => '173.65957446808522 -49.580425531914784',
                                        'RightDirection' => '173.65957446808522 -49.580425531914784'
                                    ],
                                ],
                                [
                                    '@attributes' => [
                                        'Anchor' => '173.65957446808522 -172.89957446808523',
                                        'LeftDirection' => '173.65957446808522 -172.89957446808523',
                                        'RightDirection' => '173.65957446808522 -172.89957446808523'
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'FrameFittingOption' => [
                '@attributes' => [
                    'FittingOnEmptyFrame' => 'Proportionally'
                ]
            ],
            'AnchoredObjectSetting' => [
                '@attributes' => [
                    'AnchoredPosition' => 'AboveLine',
                ]
            ],
            'Image' => [
                '@attributes' => [
                    'Self' => "{$id}a",
                    'ItemTransform' => '0.3523404255319156 0 0 0.3523404255319156 50.34042553191477 -172.89957446808523'
                ],
                'Properties' => [
                    'GraphicBounds' => [
                        '@attributes' => [
                            'Left' => '0',
                            'Top' => '0',
                            'Right' => '350',
                            'Bottom' => '350'
                        ]
                    ]
                ],
                'Link' => [
                    '@attributes' => [
                        'Self' => "{$id}0b",
                        'LinkResourceURI' => "file:Links/picture$index.png",
                        'LinkResourceFormat' => '$ID/Portable Network Graphics (PNG)'
                    ]
                ]
            ]
        ], 'Rectangle', false, true);

        $this->addLinks();
    }

    private function addLinks()
    {
        // download images
        $outDir = Designmap::$cwd;

        !is_dir("$outDir/Links") && mkdir("$outDir/Links", 0777, true);

        $file = file_get_contents($this->url);
        file_put_contents("$outDir/Links/picture$this->index.png", $file);


        $linkXml = new \DOMDocument('1.0', 'UTF-8');
        $linkXml->standalone = true;
        if(file_exists("$outDir/Resources/Links.xml")) {
            $linkXml->load("$outDir/Resources/Links.xml");
        } else {
            $root = $linkXml->createElement('idPkg:Links');
            $root->setAttribute('xmlns:idPkg', 'http://ns.adobe.com/AdobeInDesign/idml/1.0/packaging');
            $root->setAttribute('DOMVersion', '21.0');
            $linkXml->appendChild($root);
        }


        $link = $linkXml->createElement('Link');
        $link->setAttribute('Self', "{$this->id}0b");
        $link->setAttribute('Name', "picture$this->index.png");
        $link->setAttribute('LinkResourceFormat', '$ID/Portable Network Graphics (PNG)');
        $link->setAttribute('LinkResourceURI', "file:Links/picture$this->index.png");
        $link->setAttribute('Status', 'Normal');
        $linkXml->documentElement->appendChild($link);

                $link = $linkXml->createElement('Link');
        $link->setAttribute('Self', "{$this->id}02b");
        $link->setAttribute('Name', "picture$this->index.png");
        $link->setAttribute('LinkResourceFormat', '$ID/Portable Network Graphics (PNG)');
        $link->setAttribute('LinkResourceURI', "file:Links/picture2$this->index.png");
        $link->setAttribute('Status', 'Normal');
        $linkXml->documentElement->appendChild($link);

        $linkXml->formatOutput = true;
        $linkXml->preserveWhiteSpace = false;
        $linkXml->save("$outDir/Resources/Links.xml");

        $graphicXml = new \DOMDocument('1.0', 'UTF-8');
        $graphicXml->load("$outDir/Resources/Graphic.xml");

        $graphic = $graphicXml->createElement('Graphic');
        $graphic->setAttribute('Self', "{$this->id}a");
        $graphic->setAttribute('ItemTransform', '1 0 0 1 0 0');
        $graphic->setAttribute('Link', "{$this->id}0b");
        $graphic->setAttribute('LinkResourceURI', "file:Links/picture$this->index.png");
        $graphic->setAttribute('GraphicTypeName', '$ID/Portable Network Graphics (PNG)');
        $graphicXml->documentElement->appendChild($graphic);

        $graphicXml->formatOutput = true;
        $graphicXml->preserveWhiteSpace = false;
        $graphicXml->save("$outDir/Resources/Graphic.xml");
    }
}
