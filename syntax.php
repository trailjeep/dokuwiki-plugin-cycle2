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
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_cycle2 extends DokuWiki_Syntax_Plugin {
    var $dataspeed   = '500';
    var $datafx      = 'fade';
    var $datatimeout = '4000';
	var $ns          = '';
	var $w           = '600';
    var $h           = '400';

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
		global $INFO;
        switch($state) {
            case DOKU_LEXER_ENTER:
                $attributes  = strtolower(substr($match, 5, -1));
                $dataspeed   = $this->_getAttribute($attributes, "data-speed", "500");
                $datafx      = $this->_getAttribute($attributes, "data-fx", "fade");
                $datatimeout = $this->_getAttribute($attributes, "data-timeout", "4000");
				$ns          = $this->_getAttribute($attributes, "ns", str_replace(':', '/', $INFO['namespace']));
				$w           = $this->_getAttribute($attributes, "w", "600");
				$h           = $this->_getAttribute($attributes, "h", "400");
                return array($state, array($dataspeed, $datafx, $datatimeout, $ns, $w, $h));
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
                list($this->dataspeed,$this->datafx,$this->datatimeout,$this->ns,$this->w,$this->h) = $match;
                $renderer->doc .= '<div class="cycle-slideshow" ';
				$renderer->doc .= 'data-cycle-speed="'.$this->dataspeed.'" ';
				$renderer->doc .= 'data-cycle-fx="'.$this->datafx.'" ';
				$renderer->doc .= 'data-cycle-pause-on-hover="true" ';
				$renderer->doc .= 'data-cycle-auto-height="container" ';
				$renderer->doc .= 'data-cycle-center-horz="true" ';
				$renderer->doc .= 'data-cycle-center-vert="false" ';
				$renderer->doc .= 'data-cycle-loader="wait" ';
				$renderer->doc .= 'data-cycle-swipe=true" ';
				$renderer->doc .= 'data-cycle-slides="> a" ';
				$renderer->doc .= 'data-timeout="'.$this->datatimeout.'">';
				$renderer->doc .= '<div class="cycle-prev"></div>';
				$renderer->doc .= '<div class="cycle-next"></div>';
                break;
              case DOKU_LEXER_UNMATCHED :  
                $renderer->doc .= $renderer->_xmlEntities($match);
                break;
			  case DOKU_LEXER_EXIT :
				global $INFO;
				$ns = $this->ns;
                if ($ns == "") { $ns = $INFO['namespace']; }
				$ns = str_replace(':', '/', $ns);
                $dir = DOKU_INC.'data/media/'.$ns;
                $files = glob($dir."/*.{jpg,png,gif}", GLOB_BRACE);
                foreach($files as $file) {
				  $file = pathinfo($file, PATHINFO_BASENAME);
				  $renderer->doc .= '<a class="media" href="/_detail/'.$ns.'/'.$file.'" target="_blank" title="'.$ns.'/'.$file.'" rel ="noopener"><img class="media" src="/_media/'.$ns.'/'.$file.'" /></a>';
				  //$renderer->doc .= '<img class="media" src ="/_media/'.$ns.'/'.$file.'" />';
				}
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
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
