<?php
require_once("../../wikitext.php");

/**
 * The custom behaviour of templates and links needs to be defined here:
 */
class CustomParserBackend extends DefaultParserBackend {

	public function getInternalLinkInfo($info) {
		/* Take people to the right place */
		$info['dest'] = "index.php?page=".$info['dest'];
		return $info;
	}

	public function getTemplateMarkup($template) {
		return LinksTemplates::getPageText($template);
	}
}

/**
 * Example of how you might want to encapsulate a page-renderer to make use of the parser.
 */
class LinksTemplates {
	function showPage($pageName) {
		if(!$input = self::getPageText($pageName)) {
			/* 404 text */
			$input = "= 404 Not found =\nPage not found. Return to [[home]].";
		} else {
			$input = "= $pageName =\n".$input;
		}
		/* Parse it and print it */
		WikitextParser::init();
		WikitextParser::$backend = new CustomParserBackend;
		$parser = new WikitextParser($input);
		echo $parser -> result;
		echo "<hr /><b>Markup for this page:</b><pre>".htmlentities($input)."</pre>";
		echo "<b>After preprocessing:</b><pre>".htmlentities($parser -> preprocessed)."</pre>";
	}

	/**
	 * Get wikitext for a given page
	 *
	 * @param   string $pageName   Identifier for the page name
	 * @return  boolean|string     Text of the page, or false if it could not be found
	 */
	function getPageText($pageName) {
		$fn = "page/" . urlencode($pageName) . ".txt";
		if(!file_exists($fn)) {
			return false;
		} else {
			return file_get_contents($fn);
		}
	}
}

/* Figure out what page to load */
$pageName = "home";
if(isset($_REQUEST['page'])) {
	$pageName = $_REQUEST['page'];
}
LinksTemplates::showPage($pageName);

?>