<?php
/**
 * Ok, glad you are here
 * first we get a config instance, and set the settings
 * $config = HTMLPurifier_Config::createDefault();
 * $config->set('Core.Encoding', $this->config->get('purifier.encoding'));
 * $config->set('Cache.SerializerPath', $this->config->get('purifier.cachePath'));
 * if ( ! $this->config->get('purifier.finalize')) {
 *     $config->autoFinalize = false;
 * }
 * $config->loadArray($this->getConfig());
 *
 * You must NOT delete the default settings
 * anything in settings should be compacted with params that needed to instance HTMLPurifier_Config.
 *
 * @link http://htmlpurifier.org/live/configdoc/plain.html
 */

return [
    'encoding'      => 'UTF-8',
    'finalize'      => true,
    'cachePath'     => storage_path('app/purifier'),
    'cacheFileMode' => 0755,
    'settings'      => [
        'default'           => [
            'HTML.Doctype'             => 'HTML 4.01 Transitional',
            'HTML.Allowed'             => 'figure[style|class],h1,h2,h3,h4,h5,h6,b,strong,i,em,a[href|title],ul,ol,li,p[style],br,span[style|class],img[width|height|alt|src|style|class],table[cellspacing|cellpadding|style|border|style],tbody,tr,td[abbr],thead,s,sub,sup,u,math[xmlns|mathvariant|mathcolor|dir|class],msqrt[mathvariant|mathcolor],mn[mathvariant|mathcolor],msub[mathvariant|mathcolor],msup[mathvariant|mathcolor],mo[mathvariant|mathcolor|largeop],mfrac[bevelled|mathvariant|mathcolor],mrow[mathvariant|mathcolor],mi[mathvariant|mathcolor],mfenced[open|close|mathvariant|mathcolor],mroot[mathvariant|mathcolor],mover[mathvariant|mathcolor],munderover[mathvariant|mathcolor],mn[mathvariant|mathcolor],mtd[mathvariant|mathcolor],mtr[mathvariant|mathcolor],mtable[mathvariant|mathcolor],msrow[mathvariant|mathcolor],msline[mathvariant|mathcolor],mstack[mathvariant|mathcolor|charalign|stackalign],mlongdiv[mathvariant|mathcolor|charalign|charspacing|stackalign],msgroup[mathvariant|mathcolor],mstyle[mathvariant|mathcolor|displaystyle],mmultiscripts[mathvariant|mathcolor],mprescripts[mathvariant|mathcolor],none[mathvariant|mathcolor],msubsup[mathvariant|mathcolor],munder[mathvariant|mathcolor],menclose[mathvariant|mathcolor|notation],mtext[mathvariant|mathcolor],mspace[mathvariant|mathcolor|linebreak],blockquote[style],comment-start[name],comment-end[name],commentstart[name],commentend[name]',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align,width,height,border-style,border-width,margin,float',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => false,
            'CSS.MaxImgLength'         => null,
        ],
        'test'              => [
            'Attr.EnableID' => 'true',
        ],
        "youtube"           => [
            "HTML.SafeIframe"      => 'false',
            "URI.SafeIframeRegexp" => "%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/)%",
        ],
        'custom_definition' => [
            'id'         => 'html5-definitions',
            'rev'        => 1,
            'debug'      => false,
            'elements'   => [
                // http://developers.whatwg.org/sections.html
                ['section', 'Block', 'Flow', 'Common'],
                ['p', 'Block', 'Flow', 'Common'],
                ['nav', 'Block', 'Flow', 'Common'],
                ['article', 'Block', 'Flow', 'Common'],
                ['aside', 'Block', 'Flow', 'Common'],
                ['header', 'Block', 'Flow', 'Common'],
                ['footer', 'Block', 'Flow', 'Common'],

                // Content model actually excludes several tags, not modelled here
                ['address', 'Block', 'Flow', 'Common'],
                ['hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common'],

                // http://developers.whatwg.org/grouping-content.html
                ['figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common'],
                ['figcaption', 'Inline', 'Flow', 'Common'],

                // http://developers.whatwg.org/the-video-element.html#the-video-element
                ['video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
                    'src'      => 'URI',
                    'type'     => 'Text',
                    'width'    => 'Length',
                    'height'   => 'Length',
                    'poster'   => 'URI',
                    'preload'  => 'Enum#auto,metadata,none',
                    'controls' => 'Bool',
                ]],
                ['source', 'Block', 'Flow', 'Common', [
                    'src'  => 'URI',
                    'type' => 'Text',
                ]],

                // http://developers.whatwg.org/text-level-semantics.html
                ['s', 'Inline', 'Inline', 'Common'],
                ['var', 'Inline', 'Inline', 'Common'],
                ['sub', 'Inline', 'Inline', 'Common'],
                ['sup', 'Inline', 'Inline', 'Common'],
                ['mark', 'Inline', 'Inline', 'Common'],
                ['wbr', 'Inline', 'Empty', 'Core'],

                // http://developers.whatwg.org/edits.html
                ['ins', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']],
                ['del', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']],
            ],
            'attributes' => [
                ['iframe', 'allowfullscreen', 'Bool'],
                ['table', 'height', 'Text'],
                ['td', 'border', 'Text'],
                ['th', 'border', 'Text'],
                ['tr', 'width', 'Text'],
                ['tr', 'height', 'Text'],
                ['tr', 'border', 'Text'],
            ],
        ],
        'custom_attributes' => [
            ['a', 'target', 'Enum#_blank,_self,_target,_top'],
            ['mfrac', 'bevelled', 'CDATA'],
            ['mfenced', 'open', 'CDATA'],
            ['mfenced', 'close', 'CDATA'],

        ],
        'custom_elements'   => [
            ['u', 'Inline', 'Inline', 'Common'],
            ['blockquote', 'Block', 'Flow', 'Common'],


            // wiris
            // http://htmlpurifier.org/docs/enduser-customize.html
            ['math', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA', 'xmlns' => 'URI', 'dir' => 'CDATA', 'class' => 'CDATA']],
            ['msqrt', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mn', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['msub', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['msup', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mo', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA', 'largeop' => 'CDATA']],
            ['mfrac', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mrow', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mi', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mfenced', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mroot', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mover', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['munderover', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['munder', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mn', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mtd', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mtr', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mtable', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['msrow', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['msline', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mstack', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA', 'charalign' => 'CDATA', 'stackalign' => 'CDATA']],
            ['mlongdiv', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA', 'charalign' => 'CDATA', 'stackalign' => 'CDATA', 'charspacing' => 'CDATA']],
            ['msgroup', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mstyle', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA', 'displaystyle' => 'CDATA']],
            ['mmultiscripts', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mprescripts', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['none', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['msubsup', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['menclose', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA', 'notation' => 'CDATA']],
            ['mtext', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA']],
            ['mspace', 'Block', 'Flow', 'Common', ['mathvariant' => 'CDATA', 'mathcolor' => 'CDATA', 'linebreak' => 'CDATA']],

            //comments plugin
            ['comment-start', 'Inline', 'Flow', 'Common', ['name' => 'Text']],
            ['comment-end', 'Inline', 'Flow', 'Common', ['name' => 'Text']],
            ['commentstart', 'Inline', 'Flow', 'Common', ['name' => 'Text']],
            ['commentend', 'Inline', 'Flow', 'Common', ['name' => 'Text']],
            ['h2', 'Inline', 'Flow', 'Common', ['name' => 'Text']],
        ],
    ],

];
