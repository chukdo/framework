<?php namespace Chukdo\Helper;

use Chukdo\Json\Json;

/**
 * Classe helper_html
 * Fonctionnalités HTML
 *
 * @package		helper
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Html
{
    /**
     * Html constructor.
     */
    private function __construct() {}

    /**
     * @param string $value
     * @return string
     */
    public static function encode(string $value): string
    {
        return htmlentities(helper_convert::toUtf8($value), ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * @param string $value
     * @return string
     */
    public static function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5,'utf-8');
    }

    /**
     * Parse un document
     * Recherche le tag et retourne ses attributs
     *
     * @param string $buffer
     * @param string $tag
     * @return Json
     */
	public static function parseTag(string $buffer, string $tag): Json
	{
		$parse = new Json([], true);
		
		foreach (helper_data::match('/(<\s*'.$tag.'((?:\s+\w+(?:\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)\/?>)/', $buffer, true) as $match) {
			$attributes	= [];
			
			foreach (helper_data::match('/(\w+)\s*=?\s*((?:".*?"|\'.*?\'))?/', $match[1], true) as $attr) {
				if (is_string($attr)) {
					$attributes[$attr] = '';
				} else {
					$attributes[$attr[0]] = trim($attr[1], '"\'');
				}
			}

			$parse->append([
			    'tag'           => $match[0],
                'attributes'    => $attributes]
            );
		}

		return $parse;
	}

    /**
     * @param string $css
     * @return string
     */
    public static function minifyCss(string $css): string
    {
        $css = preg_replace('/(?:\/\*(?:[\s\S]*?)\*\/)|(?:^\s*\/\/(?:.*)$)/m', '', $css);
        $css = preg_replace('/[\r\n\t]/', ' ', $css);
        $css = preg_replace('/[ ]{2,}/', ' ', $css);

        return $css;
    }

    /**
     * @param string $js
     * @return string
     */
    public static function minifyJs(string $js): string
    {
        $js = preg_replace('/(?:\/\*(?:[\s\S]*?)\*\/)|(?:^\s*\/\/(?:.*)$)/m', '', $js);
        $js = preg_replace('/[\r\n\t]/', ' ', $js);
        $js = preg_replace('/[ ]{2,}/', ' ', $js);

        return $js;
    }

    /**
     * @param string $html
     * @return string
     */
    public static function minifyHtml(string $html): string
    {
    	return preg_replace(
    		[
    		    '/\n|\r|\t/',
                '/>\s{1,}</',
                '/ {1,}/'
            ], [
                '',
                '><',
                ' '
            ],
    		$html);
    }

    /**
     * @param string $html
     * @return string
     */
    public static function indentHtml(string $html): string
    {
        $str     = '';
        $index   = 0;
        $indent  = 0;
        $html    = self::minifyHtml($html);
        
        for ($i = 0; $i < strlen($html); ++$i) {
            $char = substr($html, $i, 1);
        
            switch ($char) {
                case '<' :
                if (substr($html, $index + 1, 1) == '/') {
                    --$indent;
                 
                    while(substr($html, $i, 1) != '>') {
                        ++$i;
                    }
                 
                    for ($j = 0; $j < $indent; ++$j) {
                        $str .= "\t";
                    }
                 
                    $str  .= substr($html, $index, $i + 1 - $index)."\n";
                    $index = $i + 1;
                
                } else if (substr($html, $index + 1, 1) != '/') {
                    
                    while(substr($html, $i, 1) != '>') {
                        ++$i;
                    }
                    
                    if (substr($html,$i - 1, 1) != '/') {
                        for ($j = 0; $j < $indent; ++$j) {
                            $str .= "\t";
                        }
                        
                        $str  .=substr($html, $index, $i + 1 - $index)."\n";
                        $index = $i + 1;
                        ++$indent;
                    } else {
                        for ($j = 0; $j < $indent; ++$j) {
                            $str .= "\t";
                        }
                        
                        $str  .=substr($html, $index, $i + 1 - $index)."\n";
                        $index = $i + 1;
                    }
                }
                break;
                default:
                    if (substr($html, $i + 1, 1) == '<') {
                        for ($j=  0; $j < $indent; ++$j) {
                            $str .= "\t";
                        }
                        
                        $str  .=substr($html, $index, $i + 1 - $index)."\n";
                        $index = $i + 1;
                    }
            }
        }
    
        return $str;
    }    
}