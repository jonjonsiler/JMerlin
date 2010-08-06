<?php
/**
 * @version		$Id: merlin.php 15105 2010-02-27 14:59:11Z ian $
 * @package		Joomla.Framework
 * @subpackage	    Document
 * @copyright	    Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
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
 * JDocumentRenderer_Merlin generates a feed that implements the PBS RSS2.0 specification for feeds
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
			$xml->text(htmlspecialchars($data->category, ENT_COMPAT, 'UTF-8'));
			$xml->endElement(); //end category
			$xml->writeElement('lastBuildDate', htmlspecialchars($now->toRFC822(), ENT_COMPAT, 'UTF-8') );
			$xml->writeElement('params');
		if ($data->image!=null) {
			$xml->startElement('image');
				$xml->writeElement('url',$data->image->url);
				$xml->writeElement('link',str_replace(' ','%20',$data->image->link) );
			$xml->endElement(); //end image tag
		}
		/* conditionals need to be added  to the following elements */
			$xml->writeElement('pbscontent:program_name',$data->program_name); //program Name like 'NOVA'
			$xml->writeElement('pbscontent:producing_member_station', $data->producing_station);
			$xml->writeElement('pbscontent:owner_member_station', $data->owner_station);
			$xml->writeElement('generator',$data->getGenerator());

		/* There are a couple of other properties that Joomla passes into this object by inheritance
		 * $data->webmaster
		 * $data->docs
		 * $data->ttl
		 * $data->rating
		 * $data->skipHours
		 * $data->skipDays
		 */

			
		if ($data->language!="") {
			$xml->writeElement('language',$data->language);
		}
		if ($data->copyright!="") {
			$xml->writeElement('copyright>',htmlspecialchars($data->copyright,ENT_COMPAT, 'UTF-8'));
		}
		if ($data->editorEmail!="") {
			$xml->writeElement("managingEditor",htmlspecialchars($data->editorEmail, ENT_COMPAT, 'UTF-8').' ('.htmlspecialchars($data->editor, ENT_COMPAT, 'UTF-8').")");
		}
		
		//begin items
		foreach ($data->items as $item) {
			$xml->startElement('item');
			$xml->writeElement('title', htmlspecialchars(strip_tags($item->title), ENT_COMPAT, 'UTF-8'));
			$xml->writeElement('description',strip_tags(str_replace(array("\r","\n",),' ', $item->description))); //400 characters max
			$xml->writeElement("media:description"); // A short description for the item 90 characters max
			$xml->writeElement('author', $item->author);

			$xml->startElement('category');
			$xml->writeAttribute('domain', 'PBS/taxonomy/topic');
			$xml->text(htmlspecialchars($item->category, ENT_COMPAT, 'UTF-8'));
			$xml->endElement(); //end category

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
			/* Most of the native PBS Merlin RSS Requirements can be passed as article Parameters and that way not much core hacking is needed from the programmer
			 * 
			 */
			$xml->writeElement('pbscontent:program_name',$item->program_name); //program Name like 'NOVA'
			$xml->writeElement('pbscontent:producing_member_station', $item->producing_station);
			$xml->writeElement('pbscontent:owner_member_station', $item->owner_station);
			$xml->writeElement('pbscontent:modDate');
			$xml->writeElement('dcterms:valid'); //Expiration date for the item
			$xml->writeElement('pbscontent:nola_root','NOVA');
			$xml->writeElement('pbscontent:nola_episode');
			//$xml->writeElement('pbsvideo');
			$xml->writeElement('pbscontent:type', 'webpage');
			$xml->writeElement('pbscontent:distribution','local');
			$xml->writeElement('media:rating');
			$xml->writeElement('media:keywords');
		
/*
 * Also passes $item->comments
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
