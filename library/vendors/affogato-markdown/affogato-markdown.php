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

require_once('../markdown/markdown.php');

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
				print_r($matches);
				return $this->_doFencedCodeBlocks_callback($matches);
			},
			$text);

		return $text;
	}

};

?>

