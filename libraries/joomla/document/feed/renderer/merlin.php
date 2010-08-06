<?php
/**
 * @version		$Id: merlin.php 15105 2010-02-27 14:59:11Z ian $
 * @package		Joomla.Framework
 * @subpackage	Document
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * JDocumentRenderer_Merlin is a feed that implements the PBS RSS2.0 specification for feeds
 *
 * Please note that just by using this class you won't automatically
 * produce valid atom files. For example, you have to specify either an editor
 * for the feed or an author for every single feed item.
 *
 * @package 	Joomla.Framework
 * @subpackage	Document
 * @since	1.5
 */

class JDocumentRendererMerlin extends JDocumentRenderer
{
	/**
	 * Renderer mime type
	 *
	 * @var		string
	 * @access	private
	 */
	var $_mime = "application/rss+xml";

	/**
	 * Render the feed
	 *
	 * @access public
	 * @return	string
	 */
	function render()
	{
		$now	=& JFactory::getDate();
		$data	=& $this->_doc;

		$uri =& JFactory::getURI();
		$url = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$syndicationURL =& JRoute::_('&format=feed&type=rss');
		
		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->setIndent(true);
		$xml->setIndentString("\t");
		//$xml->startDocument('1.0', 'UTF-8');
		$xml->startElement('rss');
		$xml->writeAttribute('version','2.0');
		$xml->writeAttribute('xmlns:pbscontent','http://www.pbs.org/rss/pbscontent');
		$xml->writeAttribute('xmlns:pbsvideo','http://www.pbs.org/rss/pbsvideo');
		$xml->writeAttribute('xmlns:media','http://search.yahoo.com/mrss');
		$xml->writeAttribute('xmlns:dcterms','http://purl.org/dc/terms');
		$xml->writeAttribute('xmlns:georss','http://www.georss.org/georss');
		$xml->startElement('channel');
		//begin channel information
			$xml->writeElement('title', $data->title); //required
			$xml->writeElement('link', str_replace(' ','%20',$url.$data->link) ); //required
			$xml->writeElement('description', htmlspecialchars($data->description) ); //required
			if ($data->pubDate!='') {
				$pubDate =& JFactory::getDate($data->pubDate);
				$xml->writeElement('pubDate',htmlspecialchars($pubDate->toRFC822(),ENT_COMPAT, 'UTF-8')); 
			}
			$xml->startElement('category');
			$xml->writeAttribute('domain', 'PBS/taxonomy/topic');
			$xml->text('text');
			$xml->endElement(); //end category
			$xml->writeElement('lastBuildDate', htmlspecialchars($now->toRFC822(), ENT_COMPAT, 'UTF-8') );
			
		if ($data->image!=null) {
			$xml->startElement('image');
				$xml->writeElement('url',$data->image->url);
				$xml->writeElement('link',str_replace(' ','%20',$data->image->link) );
			$xml->endElement(); //end image tag
		}
			
			$xml->writeElement('pbscontent:program_name','blank'); //program Name like 'NOVA'
			$xml->writeElement('pbscontent:producing_member_station', 'OETA');
			$xml->writeElement('pbscontent:owner_member_station', 'OETA');

		
/*		
		$feed.= "		<generator>".$data->getGenerator()."</generator>\n";
	
		if ($data->language!="") {
			$feed.= "		<language>".$data->language."</language>\n";
		}
		if ($data->copyright!="") {
			$feed.= "		<copyright>".htmlspecialchars($data->copyright,ENT_COMPAT, 'UTF-8')."</copyright>\n";
		}
		if ($data->editorEmail!="") {
			$feed.= "		<managingEditor>".htmlspecialchars($data->editorEmail, ENT_COMPAT, 'UTF-8').' ('.
				htmlspecialchars($data->editor, ENT_COMPAT, 'UTF-8').")</managingEditor>\n";
		}
		if ($data->webmaster!="") {
			$feed.= "		<webMaster>".htmlspecialchars($data->webmaster, ENT_COMPAT, 'UTF-8')."</webMaster>\n";
		}
		if ($data->category!="") {
			$feed.= "		<category>".htmlspecialchars($data->category, ENT_COMPAT, 'UTF-8')."</category>\n";
		}
		if ($data->docs!="") {
			$feed.= "		<docs>".htmlspecialchars($data->docs, ENT_COMPAT, 'UTF-8')."</docs>\n";
		}
		if ($data->ttl!="") {
			$feed.= "		<ttl>".htmlspecialchars($data->ttl, ENT_COMPAT, 'UTF-8')."</ttl>\n";
		}
		if ($data->rating!="") {
			$feed.= "		<rating>".htmlspecialchars($data->rating, ENT_COMPAT, 'UTF-8')."</rating>\n";
		}
		if ($data->skipHours!="") {
			$feed.= "		<skipHours>".htmlspecialchars($data->skipHours, ENT_COMPAT, 'UTF-8')."</skipHours>\n";
		}
		if ($data->skipDays!="") {
			$feed.= "		<skipDays>".htmlspecialchars($data->skipDays, ENT_COMPAT, 'UTF-8')."</skipDays>\n";
		}
*/
		//begin items

		foreach ($data->items as $item) {
			$xml->startElement('item');
			$xml->writeElement('title', htmlspecialchars(strip_tags($item->title), ENT_COMPAT, 'UTF-8'));
			$xml->writeElement('description',strip_tags(str_replace(array("\r","\n",),' ', $item->description)));
			if ((strpos($item->link, 'http://') === false) and (strpos($item->link, 'https://') === false)) {
				$item->link = str_replace(' ','%20',$url.$item->link);
			}
			$xml->writeElement('link',str_replace(' ','%20',$url.$item->link));
			if ($item->date != '') {
				$itemDate =& JFactory::getDate($item->date);
				$xml->writeElement('pubDate',htmlspecialchars($itemDate->toRFC822(), ENT_COMPAT, 'UTF-8'));
			}
			if ($item->guid != '') {
				$xml->writeElement('guid',htmlspecialchars($item->guid, ENT_COMPAT, 'UTF-8'));
			} else {
				$xml->writeElement('guid',str_replace(' ','%20',$item->link));
			}
			if ($item->enclosure != NULL){
				$xml->startElement('enclosure');
				$xml->writeAttribute('url', $item->enclosure->url);
				$xml->writeAttribute('length',$item->enclosure->length);
				$xml->writeAttribute('type', $item->enclosure->type);
				$xml->endElement();
			}

/*
			if ($data->items[$i]->authorEmail!="") {
				$feed.= "			<author>".htmlspecialchars( $data->items[$i]->author, ENT_COMPAT, 'UTF-8')."</author>\n";
			}
			if ($data->items[$i]->category!="") {
				$feed.= "			<category>".htmlspecialchars($data->items[$i]->category, ENT_COMPAT, 'UTF-8')."</category>\n";
			}
			if ($data->items[$i]->comments!="") {
				$feed.= "			<comments>".htmlspecialchars($data->items[$i]->comments, ENT_COMPAT, 'UTF-8')."</comments>\n";
			}

*/
			$xml->endElement();
		}
		//end items
		$xml->endElement(); //end channel feed
		//end channel information

		$xml->endElement(); //end RSS feed
		//	$xml->endDocument();
		return $xml->flush(true); //return a string
	}

	/**
	 * Convert links in a text from relative to absolute
	 *
	 * @access public
	 * @return	string
	 */
	function _relToAbs($text)
	{
		$base = JURI::base();
  		$text = preg_replace("/(href|src)=\"(?!http|ftp|https|mailto)([^\"]*)\"/", "$1=\"$base\$2\"", $text);

		return $text;
	}
}
