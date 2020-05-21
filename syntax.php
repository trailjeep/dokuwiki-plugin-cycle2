<?php
/**
 * Plugin cycle2: versatile slideshow plugin for jQuery
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Reinhard Kaeferboeck <rfk@kaeferboeck.info>
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_INC.'inc/JpegMeta.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_cycle2 extends DokuWiki_Syntax_Plugin {

    function getType(){ return 'formatting';}
    function getPType(){ return 'normal';}
    function getAllowedTypes() { return array('container','substition','protected','disabled','formatting','paragraphs'); }
    function getSort(){ return 195; }
	function connectTo($mode) { $this->Lexer->addEntryPattern('<cycle2.*?>(?=.*?</cycle2>)',$mode,'plugin_cycle2'); }
    function postConnect() { $this->Lexer->addExitPattern('</cycle2>','plugin_cycle2'); }
  
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        switch($state) {
            case DOKU_LEXER_ENTER:
				//$attributes  = strtolower(substr($match, 5, -1));
				$attributes  = substr($match, 5, -1);
                $speed     = $this->_getAttribute($attributes, "speed", "500");
                $fx        = $this->_getAttribute($attributes, "fx", "fade");
                $timeout   = $this->_getAttribute($attributes, "timeout", "4000");
				$namespace = $this->_getAttribute($attributes, "namespace", str_replace(':', '/', $INFO['namespace']));
				$width     = $this->_getAttribute($attributes, "width", "400px");
                return array($state, array($speed, $fx, $timeout, $namespace, $width));
            case DOKU_LEXER_UNMATCHED:
                return array($state, $match);
            case DOKU_LEXER_EXIT:
                return array($state, '');
        }
        return array();
    }
 
    /**
     * Create output
     */
    function render($mode, Doku_Renderer $renderer, $data) {
        if($mode == 'xhtml'){
            list($state,$match) = $data;
            switch ($state) {
              case DOKU_LEXER_ENTER :
                list($this->speed,$this->fx,$this->timeout,$this->namespace,$this->width) = $match;
                $renderer->doc .= '<div class="cycle-slideshow" style="width: '.$this->width.';" ';
				$renderer->doc .= 'data-cycle-speed="'.$this->speed.'" ';
				$renderer->doc .= 'data-cycle-fx="'.$this->fx.'" ';
				$renderer->doc .= 'data-cycle-pause-on-hover="true" ';
				$renderer->doc .= 'data-cycle-auto-height="container" ';
				$renderer->doc .= 'data-cycle-center-horz="true" ';
				$renderer->doc .= 'data-cycle-center-vert="false" ';
				$renderer->doc .= 'data-cycle-loader="wait" ';
				$renderer->doc .= 'data-cycle-swipe="true" ';
				$renderer->doc .= 'data-cycle-random="true" ';
				$renderer->doc .= 'data-cycle-slides="> a" ';
				$renderer->doc .= 'data-timeout="'.$this->timeout.'">';
				$renderer->doc .= '<div class="cycle-prev"></div>';
				$renderer->doc .= '<div class="cycle-next"></div>';
				$renderer->doc .= '<div class="cycle-overlay center"></div>';
                break;
              case DOKU_LEXER_UNMATCHED :  
                $renderer->doc .= $renderer->_xmlEntities($match);
                break;
			  case DOKU_LEXER_EXIT :
				$images = $this->_getNsImages($this->namespace);
				$renderer->doc .= $images;
                $renderer->doc .= "</div>"; 
                break;
            }
            return true;
        }
        return false;
    }
	
    function _getAttribute($attributeString, $attribute, $default){
        $retVal = $default;
        $pos = strpos($attributeString, $attribute."=");
        if ($pos === false) {
            $pos = strpos($attributeString, $attribute." ");
        }
        if ($pos > 0) {
            $pos = $pos + strlen($attribute);
            $value = substr($attributeString,$pos);
            
            //replace '=' and quote signs with null and trim leading spaces
			$value = ltrim(str_replace(['=', "'", '"'], '', $value));
            
            //grab the text before the next space
            $pos = strpos($value, " ");
            if ($pos > 0) {
                $value = substr($value,0,$pos);
            }
            
            $retVal = hsc($value);
        }
        return $retVal;
    }
    function _getNsImages($ns) {
		global $conf;
        $files = array();
		$images = '';
		if ($ns == ".") {
			$ns = getNS(cleanID(getID()));
		} elseif ($ns == "") {
			return false;
		}
		$ns     = str_replace(':', '/', $ns);
		$files  = glob($conf['mediadir'].'/'.$ns."/*.{jp*g,png,gif}", GLOB_BRACE);
		require_once(DOKU_INC.'inc/JpegMeta.php');
       	foreach($files as $file) {
			$base = pathinfo($file, PATHINFO_BASENAME);
			$meta = new JpegMeta($file);
			$title = $meta->getField('Simple.Title');
			$desc = $meta->getField('Iptc.Caption');
			$images .= '<a class="media" href="/_detail/'.$ns.'/'.$base.'" target="_blank" title="'.$ns.'/'.$base.'" rel ="noopener" data-cycle-title="'.$title.'" data-cycle-desc="'.$desc.'">';
			$images .= '<img class="media" width="'.$this->width.'" src=" /_media/'.$ns.'/'.$base.'" data-cycle-title="'.$title.'" data-cycle-desc="'.$desc.'" />';
			$images .= '</a>';
       	}
		return $images;
	}
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
