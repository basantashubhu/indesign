<?php

namespace Santa\Indesign;

class Spread
{
    use XmlHelper;

    protected $document;
    protected static $textFrameId = 1000;
    public $file;

    public function __construct($file)
    {
        $this->document = new \DOMDocument('1.0', 'UTF-8');
        $this->document->standalone = true;
        file_exists(Designmap::$cwd . "/Spreads/$file") && $this->document->load(Designmap::$cwd . "/Spreads/$file");
        $this->file = $file;
    }

    public static function make()
    {
        $id = uniqid();
        $spread = new self("Spread_u$id.xml");
        $spread->document->loadXML(ArrayToXml::convert([
            '@attributes' => [
                'xmlns:idPkg' => 'http://ns.adobe.com/AdobeInDesign/idml/1.0/packaging',
                'DOMVersion' => '21.0'
            ],
            'Spread' => [
                '@attributes' => [
                    'Self' => "u$id",
                    'PageTransitionType' => 'None',
                    'PageTransitionDirection' => 'NotApplicable',
                    'PageTransitionDuration' => 'Medium',
                    'ShowMasterItems' => 'true',
                    'PageCount' => '1',
                    'BindingLocation' => '0',
                    'SpreadHidden' => 'false',
                    'AllowPageShuffle' => 'false',
                    'ItemTransform' => '1 0 0 1 0 0', // explaination: 1 0 0 1 0 0 means no transform
                    'FlattenerOverride' => 'Default'
                ],
                'FlattenerPreference' => [
                    '@attributes' => [
                        'LineArtAndTextResolution' => '300',
                        'GradientAndMeshResolution' => '150',
                        'ClipComplexRegions' => 'false',
                        'ConvertAllStrokesToOutlines' => 'false',
                        'ConvertAllTextToOutlines' => 'false'
                    ],
                    'Properties' => [
                        'RasterVectorBalance' => [
                            '@attributes' => [
                                'type' => 'double'
                            ],
                            '@value' => '50'
                        ]
                    ]
                ],
                'Page' => [
                    '@attributes' => [
                        'Self' => "p$id",
                        'TabOrder' => '',
                        'AppliedMaster' => 'ub9',
                        'OverrideList' => '',
                        'MasterPageTransform' => '1 0 0 1 0 0',
                        'Name' => '1',
                        'AppliedTrapPreset' => 'TrapPreset/$ID/kDefaultTrapStyleName',
                        'GeometricBounds' => '0 0 990 751.5',
                        'ItemTransform' => '1 0 0 1 0 -495',
                        'AppliedAlternateLayout' => 'ub4',
                        'LayoutRule' => 'Off',
                        'SnapshotBlendingMode' => 'IgnoreLayoutSnapshots',
                        'OptionalPage' => 'false',
                        'GridStartingPoint' => 'TopOutside',
                        'UseMasterGrid' => 'true'
                    ],
                    'Properties' => [
                        'PageColor' => [
                            '@attributes' => [
                                'type' => 'enumeration'
                            ],
                            '@value' => 'UseMasterColor'
                        ],
                        'Descriptor' => [
                            'ListItem' => [
                                ['@attributes' => ['type' => 'string'], '@value' => ''],
                                ['@attributes' => ['type' => 'enumeration'], '@value' => 'Arabic'],
                                ['@attributes' => ['type' => 'boolean'], '@value' => 'true'],
                                ['@attributes' => ['type' => 'boolean'], '@value' => 'false'],
                                ['@attributes' => ['type' => 'long'], '@value' => '1'],
                                ['@attributes' => ['type' => 'long'], '@value' => '1'],
                                ['@attributes' => ['type' => 'string'], '@value' => '']
                            ]
                        ],
                    ],
                    'Guide' => [
                        '@attributes' => [
                            'Self' => "g$id",
                            'OverriddenPageItemProps' => '',
                            'Orientation' => 'Vertical',
                            'Location' => '704.7',
                            'FitToPage' => 'true',
                            'ViewThreshold' => '5',
                            'Locked' => 'false',
                            'ItemLayer' => 'ub8',
                            'PageIndex' => '2',
                            'GuideType' => 'Ruler',
                            'GuideZone' => '1'
                        ],
                        'Properties' => [
                            'GuideColor' => [
                                '@attributes' => [
                                    'type' => 'enumeration'
                                ],
                                '@value' => 'Cyan'
                            ]
                        ]
                    ],
                    'MarginPreference' => [
                        '@attributes' => [
                            'ColumnCount' => '12',
                            'ColumnGutter' => '12',
                            'Top' => '48.24',
                            'Bottom' => '54',
                            'Left' => '48.24',
                            'Right' => '46.8',
                            'ColumnDirection' => 'Horizontal',
                            'ColumnsPositions' => '0 43.705000000000005 55.705000000000005 99.41000000000001 111.41000000000001 155.115 167.115 210.82000000000002 222.82000000000002 266.52500000000003 278.52500000000003 322.23 334.23 377.935 389.935 433.64 445.64 489.34499999999997 501.34499999999997 545.05 557.05 600.755 612.755 656.46',
                        ]
                    ],
                    'GridDataInformation' => [
                        '@attributes' => [
                            'FontStyle' => 'Regular',
                            'PointSize' => '12',
                            'CharacterAki' => '0',
                            'LineAki' => '9',
                            'HorizontalScale' => '100',
                            'VerticalScale' => '100',
                            'LineAlignment' => 'LeftOrTopLineJustify',
                            'GridAlignment' => 'AlignEmCenter',
                            'CharacterAlignment' => 'AlignEmCenter'
                        ],
                        'Properties' => [
                            'AppliedFont' => [
                                '@attributes' => [
                                    'type' => 'string'
                                ],
                                '@value' => 'Minion Pro'
                            ]
                        ]
                    ]
                ],
                'TextFrame' => [
                    '@attributes' => [
                        'Self' => "tf". static::$textFrameId++,
                        'ParentStory' => 'u2b8',
                        'PreviousTextFrame' => 'n',
                        'NextTextFrame' => "tf" . static::$textFrameId,
                        'ContentType' => 'TextType',
                        'OverriddenPageItemProps' => '',
                        'FlexItemWidthMode' => 'FlexFixed',
                        'FlexItemHeightMode' => 'FlexFixed',
                        'Visible' => 'true',
                        'Name' => '$ID/',
                        'HorizontalLayoutConstraints' => 'FlexibleDimension FixedDimension FlexibleDimension',
                        'VerticalLayoutConstraints' => 'FlexibleDimension FixedDimension FlexibleDimension',
                        'GradientFillStart' => '0 0',
                        'GradientFillLength' => '0',
                        'GradientFillAngle' => '0',
                        'GradientStrokeStart' => '0 0',
                        'GradientStrokeLength' => '0',
                        'GradientStrokeAngle' => '0',
                        'ItemLayer' => 'ub8',
                        'Locked' => 'false',
                        'LocalDisplaySetting' => 'Default',
                        'GradientFillHiliteLength' => '0',
                        'GradientFillHiliteAngle' => '0',
                        'GradientStrokeHiliteLength' => '0',
                        'GradientStrokeHiliteAngle' => '0',
                        'AppliedObjectStyle' => 'ObjectStyle/$ID/[Normal Text Frame]',
                        'ItemTransform' => '1 0 0 1 339.7158157884935 -17.63999999999919',
                        'ParentInterfaceChangeCount' => '',
                        'TargetInterfaceChangeCount' => '',
                        'LastUpdatedInterfaceChangeCount' => ''
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
                                                'Anchor' => '-291.6576 -393.36000000000075',
                                                'LeftDirection' => '-291.6576 -393.36000000000075',
                                                'RightDirection' => '-291.6576 -393.36000000000075'
                                            ]
                                        ],
                                        [
                                            '@attributes' => [
                                                'Anchor' => '-291.6576 458.6399999999992',
                                                'LeftDirection' => '-291.6576 458.6399999999992',
                                                'RightDirection' => '-291.6576 458.6399999999992'
                                            ]
                                        ],
                                        [
                                            '@attributes' => [
                                                'Anchor' => '365.5224 458.6399999999992',
                                                'LeftDirection' => '365.5224 458.6399999999992',
                                                'RightDirection' => '365.5224 458.6399999999992'
                                            ]
                                        ],
                                        [
                                            '@attributes' => [
                                                'Anchor' => '365.5224 -393.36000000000075',
                                                'LeftDirection' => '365.5224 -393.36000000000075',
                                                'RightDirection' => '365.5224 -393.36000000000075'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'ObjectExportOption' => [
                        '@attributes' => [
                            'AltTextSourceType' => 'SourceXMLStructure',
                            'ActualTextSourceType' => 'SourceXMLStructure',
                            'CustomAltText' => '$ID/',
                            'CustomActualText' => '$ID/',
                            'ApplyTagType' => 'TagFromStructure',
                            'ImageConversionType' => 'JPEG',
                            'ImageExportResolution' => 'Ppi300',
                            'GIFOptionsPalette' => 'AdaptivePalette',
                            'GIFOptionsInterlaced' => 'true',
                            'JPEGOptionsQuality' => 'High',
                            'JPEGOptionsFormat' => 'BaselineEncoding',
                            'ImageAlignment' => 'AlignLeft',
                            'ImageSpaceBefore' => '0',
                            'ImageSpaceAfter' => '0',
                            'UseImagePageBreak' => 'false',
                            'ImagePageBreak' => 'PageBreakBefore',
                            'CustomImageAlignment' => 'false',
                            'SpaceUnit' => 'CssPixel',
                            'CustomLayout' => 'false',
                            'CustomLayoutType' => 'AlignmentAndSpacing',
                            'EpubType' => '$ID/',
                            'SizeType' => 'DefaultSize',
                            'CustomSize' => '$ID/',
                            'PreserveAppearanceFromLayout' => 'PreserveAppearanceDefault',
                            'EpubAriaRole' => '$ID/'
                        ],
                        'Properties' => [
                            'AltMetadataProperty' => [
                                '@attributes' => [
                                    'NamespacePrefix' => '$ID/',
                                    'PropertyPath' => '$ID/'
                                ]
                            ],
                            'ActualMetadataProperty' => [
                                '@attributes' => [
                                    'NamespacePrefix' => '$ID/',
                                    'PropertyPath' => '$ID/'
                                ]
                            ]
                        ]
                    ],
                    'TextFramePreference' => [
                        '@attributes' => [
                            'TextColumnCount' => '4',
                            'TextColumnFixedWidth' => '155.29500000000002',
                            'TextColumnMaxWidth' => '0'
                        ],
                        'Properties' => [
                            'InsetSpacing' => [
                                '@attributes' => [
                                    'type' => 'list'
                                ],
                                'ListItem' => [
                                    ['@attributes' => ['type' => 'unit'], '@value' => '0'],
                                    ['@attributes' => ['type' => 'unit'], '@value' => '0'],
                                    ['@attributes' => ['type' => 'unit'], '@value' => '0'],
                                    ['@attributes' => ['type' => 'unit'], '@value' => '0']
                                ]
                            ]
                        ]
                    ],
                    'TextWrapPreference' => [
                        '@attributes' => [
                            'Inverse' => 'false',
                            'ApplyToMasterPageOnly' => 'false',
                            'TextWrapSide' => 'BothSides',
                            'TextWrapMode' => 'None'
                        ],
                        'Properties' => [
                            'TextWrapOffset' => [
                                '@attributes' => [
                                    'Top' => '0',
                                    'Left' => '0',
                                    'Bottom' => '0',
                                    'Right' => '0'
                                ]
                            ]
                        ]
                    ]
                ],
            ]
        ], 'idPkg:Spread'));

        return $spread;
    }

    public static function all()
    {
        $dir = Designmap::$cwd . '/Spreads';

        return array_map(function ($file) {
            return new self(basename($file));
        }, glob("$dir/*.xml"));
    }

    public function save()
    {
        $this->document->formatOutput = true;
        $this->document->preserveWhiteSpace = false;
        $this->document->save(Designmap::$cwd . "/Spreads/$this->file");
    }
}
