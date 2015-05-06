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
                return $this->CreateSketch($matches);
            },
            $text);

        return $text;
    }

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

    function CreateSketch($matches)
    {
        $sID = uniqid();

        $sProcessingCode = str_replace('"', "'", json_encode($matches[2], JSON_HEX_QUOT));
        $nDocumentRootLength = count(explode(DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'])) + 1;
        $sRootUrl = '/' . implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, __FILE__), $nDocumentRootLength, -4));

        return "<p>                                                                           \r\n"
        .      "<div style='resize:both; overflow: hidden; border: 1px solid black'>          \r\n"
        .      "    <iframe sandbox='allow-scripts' style='width:100%; height 100%;' scrolling='no' srcdoc=\"   \r\n"
        .      "        <html>                                                                \r\n"
        .      "        <head>                                                                \r\n"
        .      "            <title>sketch</title>                                             \r\n"
        .      "            <script src='{$sRootUrl}/js/library/jquery.js'></script>          \r\n"
        .      "            <script src='{$sRootUrl}/library/vendors/affogato-markdown/processing.js'></script>\r\n"
        .      "        </head>                                                               \r\n"
        .      "        <body style='margin: 0px'>                                            \r\n"
        .      "            <table id='play-{$sID}' style='width: 100%'>                      \r\n"
        .      "                <tr style='height: 150px'>                                    \r\n"
        .      "                    <td style='text-align:center'>                            \r\n"
        .      "                        &#9654;                                               \r\n"
        .      "                    </td>                                                     \r\n"
        .      "                </tr>                                                         \r\n"
        .      "            </table>                                                          \r\n"
        .      "            <canvas id='{$sID}'>                                              \r\n"
        .      "            </canvas>                                                         \r\n"
        .      "            <script>                                                          \r\n"
        .      "                $(document).ready(function()                                  \r\n"
        .      "                {                                                             \r\n"
        .      "                    if ('sandbox' in document.createElement('iframe'))        \r\n"
        .      "                    {                                                         \r\n"
        .      "                        $('body').one('click', function()                     \r\n"
        .      "                        {                                                     \r\n"
        .      "                            $('#play-{$sID}').remove();                       \r\n"
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
        .      "</div>                                                                        \r\n"
        .      "<A href='.' id='showCode{$sID}'>Show code</A>                                 \r\n"
        .      "<div id='theCode{$sID}' style='position: relative; top: -10px'>               \r\n"
        .      $this->_doFencedCodeBlocks_callback($matches)
        .      "</div>                                                                        \r\n"
        .      "<script>                                                                      \r\n"
        .      "    $(document).ready(function()                                              \r\n"
        .      "    {                                                                         \r\n"
        .      "        $('#theCode{$sID}').hide();                                           \r\n"
        .      "        $('#showCode{$sID}').toggle(function()                                \r\n"
        .      "        {                                                                     \r\n"
        .      "            $('#theCode{$sID}').show();                                       \r\n"
        .      "            $('#showCode{$sID}').html('Hide code');                           \r\n"
        .      "            return false;                                                     \r\n"
        .      "        }, function()                                                         \r\n"
        .      "        {                                                                     \r\n"
        .      "            $('#theCode{$sID}').hide();                                       \r\n"
        .      "            $('#showCode{$sID}').html('Show code');                           \r\n"
        .      "            return false;                                                     \r\n"
        .      "        });                                                                   \r\n"
        .      "    });                                                                       \r\n"
        .      "</script>                                                                     \r\n"
        .      "</p>                                                                          \r\n";
    }

};

?>

