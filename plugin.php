<?php

/**
 * @see README.mb for further details
 *
 * @package Phile
 * @subpackage PhileTableOfContents
 * @version 1.0
 * @author mcbSolutions.at <dev@mcbsolutions.at>
 */
class PhileTableOfContents extends \Phile\Plugin\AbstractPlugin implements \Phile\EventObserverInterface {

    // default settings
    private $depth = 3;
    private $min_headers = 3;
    private $top_txt = 'Top';
    private $caption = '';
    private $anchor = false;
    private $top_link;
    // internal
    private $toc = '';
    private $xpQuery;

    private $config;

    public function __construct() {
        \Phile\Event::registerEvent('config_loaded', $this);
        \Phile\Event::registerEvent('template_engine_registered', $this);
        \Phile\Event::registerEvent('after_parse_content', $this);
        $this->config = \Phile\Registry::get('Phile_Settings');
    }

    public function on($eventKey, $data = null) {
        // check $eventKey for which you have registered
        switch ($eventKey) {
            case 'config_loaded':
                $this->config_loaded();
                break;
            case 'template_engine_registered':
                $this->export_twig_filter($data['engine']);
                break;
            case 'after_parse_content':
                $this->after_parse_content($data['content']);
                $this->export_twig_vars();
            break;
        }
    }

    private function export_twig_filter($twig_engine) {
        $excerpt = new Twig_SimpleFilter('toc_excerpt', function ($string){
            // Remove "Top" links so that they don't appear in the excerpt
            $string = str_replace($this->top_link, '', $string);
            // Return first paragraph
            return strip_tags(substr($string, strpos($string, '<p'), strpos($string, '</p>') + 4));
        });
        $twig_engine->addFilter($excerpt);
    }

    private function makeToc(&$content)
    {
        //get the headings
        if (preg_match_all('/<h[1-'.$this->depth.']{1,1}[^>]*>.*?<\/h[1-'.$this->depth.']>/s',$content,$headers) === false)
            return "";

        //create the toc
        $heads = implode("\n",$headers[0]);
        $heads = preg_replace('/<a.+?\/a>/','',$heads);
        $heads = preg_replace('/<h([1-6]) id="?/','<li class="toc$1"><a href="#',$heads);
        $heads = preg_replace('/<\/h[1-6]>/','</a></li>',$heads);

        $cap = $this->caption == '' ? "" : '<p id="toc-header">'.$this->caption.'</p>';

        return '<div id="toc">'.$cap.'<ul>'.$heads.'</ul></div>';
    }

    private function config_loaded() {
        // merge the arrays to bind the settings to the view
        // Note: this->config takes precedence
        $this->config = array_merge($this->settings, $this->config);

        if (isset($this->config['toc_depth']))
            $this->depth = &$this->config['toc_depth'];
        if (isset($this->config['toc_min_headers']))
            $this->min_headers = &$this->config['toc_min_headers'];
        if (isset($this->config['toc_top_txt']))
            $this->top_txt = &$this->config['toc_top_txt'];
        if (isset($this->config['toc_caption']))
            $this->caption = &$this->config['toc_caption'];
        if (isset($this->config['toc_anchor']))
            $this->anchor = &$this->config['toc_anchor'];
        if (isset($this->config['top_link']))
            $this->top_link = &$this->config['top_link'];

        for ($i=1; $i <= $this->depth; $i++) {
            $this->xpQuery[] = "//h$i";
        }
        $this->xpQuery = join("|", $this->xpQuery);

        $this->top_link = '<a href="#top" class="toc-nav">'.$this->top_txt.'</a>';
    }

    private function after_parse_content(&$content) {
        if (trim($content) == '') return;
        // Workaround from cbuckley:
        // "... an alternative is to prepend the HTML with an XML encoding declaration, provided that the
        // document doesn't already contain one:
        //
        // http://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly
        $domdoc = new DOMDocument();
        $domdoc->loadHTML('<?xml encoding="utf-8" ?>' . $content);
        $xp = new DOMXPath($domdoc);

        $nodes =$xp->query($this->xpQuery);

        if($nodes->length < $this->min_headers)
            return;
        // add missing id's to the h tags
        $id = 0;
        foreach($nodes as $i => $sort)
        {
            if (isset($sort->tagName) && $sort->tagName !== '')
            {
                if($sort->getAttribute('id') === "")
                {
                    ++$id;
                    $sort->setAttribute('id', "toc_head$id");
                }
                $a = $domdoc->createElement('a', $this->top_txt);
                $a->setAttribute('href', '#top');
                $a->setAttribute('class', 'toc-nav');
                $sort->appendChild($a);
            }
        }
        // add top anchor
        if($this->anchor)
        {
            $body = $xp->query("//body/node()")->item(0);
            $a = $domdoc->createElement('a');
            $a->setAttribute('name', 'top');
            $body->parentNode->insertBefore($a, $body);
        }

        $content = preg_replace(
                array("/<(!DOCTYPE|\?xml).+?>/", "/<\/?(html|body)>/"),
                array(                         "",                   ""),
                $domdoc->saveHTML()
                );

        $this->toc = $this->makeToc($content);
    }

    private function export_twig_vars() {
        if (\Phile\Registry::isRegistered('templateVars')) {
            $twig_vars = \Phile\Registry::get('templateVars');
        } else {
            $twig_vars = array();
        }
        $twig_vars['toc'] = $this->toc;
        $twig_vars['toc_top'] = $this->anchor ? "" : '<a id="top"></a>';
        $twig_vars['top_link'] = $this->top_link;
        \Phile\Registry::set('templateVars', $twig_vars);
    }
}
