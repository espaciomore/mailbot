<?php
namespace Mailbot;

abstract class Inliner
{ 
  public static function transform($html)
  {
    $document = \phpQuery::newDocumentHTML($html);
    \phpQuery::selectDocument($document);
    $style = $document->find('style')->text();
    $css = static::stripComments($style);
    $selectors = static::findSelectors($css);

    foreach ($selectors as $i => $s) {
      foreach( $document->find($s['sel']) as $element ) {
        $props = array();
        foreach ($s['props'] as $i => $p) {
          $props[] = $p['name'].': '.preg_replace('/"(.*)"/',"'$1'",$p['value']).';';
        }
        \phpQuery::pq($element)->attr('style',implode('', $props));
      };
    }

    return $document->htmlOuter();
  }

  public static function stripComments($html)
  {
    return preg_replace("/\/\*.*\*\//misU",'', $html);
  }

  public static function findSelectors($css)
  {
    $selectors = array();
    $tmp = array();
    $w = "";
    foreach (str_split($css) as $i => $c) {
      switch ($c) {
        case '{':
          $a = array();
          foreach ($tmp as $j => $s) {
            $a[] = $s['sel'];
          }
          $a[] = trim($w);
          $sel = '';
          foreach ($a as $j => $name) {
            if ( $name[0] === '#' or $name[0] === '.') {
              $sel = $sel . $name;
            } else {
              $sel = $sel . ' ' . $name;
            }
          }
          $tmp[] = array('sel'=>$sel,'props'=>array());
          break;
        case ';':
          $kv = split(':',trim($w));
          $sel = array_pop($tmp);
          $sel['props'][] = array('name'=>$kv[0],'value'=>$kv[1]);
          array_push($tmp, $sel);
          break;
        case '}':
          $selectors[] = array_pop($tmp);
          break;
        default:
          $w = "{$w}{$c}";
          continue 2;
      }
      $w = "";
    }
    
    return $selectors;
  }
}
