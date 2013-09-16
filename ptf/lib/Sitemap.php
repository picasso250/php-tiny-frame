<?php
/**
 * 生成 Google 家的 SiteMap
 * 作者：picasso250
 * 2012年9月27日
 *
 * Usage:
 * $s = new Sitemap();
 * $s->add(array(
 *     'loc' => 'http://example.com/',
 * );
 * $filename = 'sitemap.xml';
 * file_put_contents($filename, $s->toString());
 */

namespace kindcent;

class Sitemap {
    
    private $urls = array();
    
    private static function _tag($arr, $tagName) {
        if (isset($arr[$tagName])) {
            return '<' . $tagName . '>' . $arr[$tagName] . '</' . $tagName . '>' . "\n";
        } else {
            return '';
        }
    }

    public function add($arr) {
        $this->urls[] = '<url>' 
                . self::_tag($arr, 'loc')
                . self::_tag($arr, 'lastmod')
                . self::_tag($arr, 'changefreq')
                . self::_tag($arr, 'priority')
                . '</url>';
    }
    
    public function toString() {
        $head_str = '<?xml version="1.0" encoding="UTF-8"?>';
        $urlset_start = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $urlset_end = '</urlset>';
        return $head_str . "\n" 
                . $urlset_start . "\n"
                . implode("\n", $this->urls)
                . $urlset_end . "\n";
    }
}
?>
