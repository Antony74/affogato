<?php

//
// affogato-markdown
//
// The markdown format used here is a small extension to vanilla-markdown, while
// at the same time being a subset of Git Flavored Markdown.  If any originality
// is to be found, then it's in the output format, which allows code written
// in the Processing(.js) language to be run in-browser.
//

@define( 'MARKDOWN_PARSER_CLASS',  'AffogatoMarkdown_Parser' );

require_once(dirname(__FILE__) . '/../markdown/markdown.php');

class AffogatoMarkdown_Parser extends MarkdownExtra_Parser
{
    function AffogatoMarkdown_Parser()
    {
        # Insert extra document, block, transformation. 
        # Parent constructor will do the sorting.
        $this->document_gamut += array("doProcessingCodeBlocks" => 3);
        $this->block_gamut += array("doProcessingCodeBlocks" => 3);

        parent::MarkdownExtra_Parser();
    }

    function doProcessingCodeBlocks($text)
    {
        $text = preg_replace_callback(
            '{
                (?:\n|\A)
                # 1: Opening marker
                (`{3,}[ ]*[P|p][R|r][O|o][C|c][E|e][S|s][S|s][I|i][N|n][G|g])

                [ ]* \n # Whitespace and newline following marker.
                
                # 2: Content
                (
                    (?>
                        (?!(`{3,}) [ ]* \n)	# Not a closing marker.
                        .*\n+
                    )+
                )
                
                # Closing marker.
                (`{3,}) [ ]* \n
            }xm',
            function($matches)
            {
                return CreateSketch($matches[2]);
            },
            $text);

        return $text;
    }

};

//
// CreateSketch
//
// This is a bit awkward because we there are four levels of scripts:
//
// 1. PHP
// 2. JavaScript in the main window
// 3. JavaScript in the iframe
// 4. The code for the Processing sketch itself
//

function CreateSketch($sRawProcessingCode)
{
    $sID = uniqid();

    $sProcessingCode = str_replace('"', "'", json_encode($sRawProcessingCode, JSON_HEX_QUOT));
    $nDocumentRootLength = count(explode(DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'])) + 1;
    $sRootUrl = '/' . implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, __FILE__), $nDocumentRootLength, -4));

    return "<p>                                                                           \r\n"
    .      "    <iframe sandbox='allow-scripts' style='border: 1px solid black' srcdoc=\"                \r\n"
    .      "        <html>                                                                \r\n"
    .      "        <head>                                                                \r\n"
    .      "            <title>sketch</title>                                             \r\n"
    .      "            <script src='{$sRootUrl}/js/library/jquery.js'></script>          \r\n"
    .      "            <script src='{$sRootUrl}/library/vendors/affogato-markdown/processing.js'></script>\r\n"
    .      "        </head>                                                               \r\n"
    .      "        <body>                                                                \r\n"
    .      "            <canvas id='{$sID}'>                                              \r\n"
    .      "            </canvas>                                                         \r\n"
    .      "            <script>                                                          \r\n"
    .      "                $(document).ready(function()                                  \r\n"
    .      "                {                                                             \r\n"
    .      "                    if ('sandbox' in document.createElement('iframe'))        \r\n"
    .      "                    {                                                         \r\n"
    .      "                        $('body').one('click', function()                     \r\n"
    .      "                        {                                                     \r\n"
    .      "                            new Processing('{$sID}', {$sProcessingCode});     \r\n"
    .      "                        });                                                   \r\n"
    .      "                    }                                                         \r\n"
    .      "                    else                                                      \r\n"
    .      "                    {                                                         \r\n"
    .      "                        $('body').html('Sorry, your browser does not support iframe sandbox'); \r\n"
    .      "                    }                                                         \r\n"
    .      "               });                                                            \r\n"
    .      "           </script>                                                          \r\n"
    .      "        </body>                                                               \r\n"
    .      "        </html>\">                                                            \r\n"
    .      "        Sorry, your browser does not support iframes                          \r\n"
    .      "    </iframe>                                                                 \r\n"
    .      "</p>                                                                          \r\n";
}

?>

