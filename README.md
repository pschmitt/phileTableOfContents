Table Of Contents
=============================================================================

This is a [Phile](http://philecms.github.io/Phile) port of [mcb_TableOfContent](https://github.com/mcbSolutions/Pico-Plugins/tree/master/mcb_TableOfContent) by [mcbSolutions](https://github.com/mcbSolutions).

Original Copyright notice:
Released under the [MIT license](http://opensource.org/licenses/MIT). Copyright (c) 2013 mcbSolutions.at

**Version** 0.1 alpha; Please report errors.

**Generates a table of contents for the current page.**

Installation
=============================================================================
1. Copy/save the plugin into `plugins` folder
2. Activate it in `config.php`: 
```php
$config['plugins'] = array(
    // [...]
    'phileUsers' => array('active' => true),
); 
```

index.html
-----------------------------------------------------------------------------
* To the `head` of your layout file add:

```html
<link rel="stylesheet" href="{{ base_url }}/plugins/phileTableOfContent/style.css" media="screen,projection,print">
<link rel="stylesheet" href="{{ base_url }}/plugins/phileTableOfContent/print.css" media="print">
```

* **Optional - Smooth scrolling:** Add 

```html
<script src="{{ base_url }}/vendor/jquery/jquery.min.js"></script>
<script src="{{ base_url }}/plugins/phileTableOfContent/code.js"></script>
```

* Add `{{ toc_top }}` directly after the `body` tag.
* Add `{{ toc }}` where you want the table of contents displayed.
* Add `{{ top_link }}` if you want a link to top outside the content.
    
Optional: Config
-----------------------------------------------------------------------------

### toc_depth
**integer**

Only display header h1 to h`n` (where `n` is 1-6)

	$config['toc_depth']		= 3;
	
### toc_min_headers
**integer**

Only generate Table of content with at least `n` headers

	$config['toc_min_headers']	= 3;	
	
### toc_top_txt					
**string**

Text to display for "Move to top"

	$config['toc_top_txt']		= 'Top';				
	
### toc_caption
**string**

Text to display as caption for the table of contents

	$config['toc_caption']		= 'Table of contents';
	
### toc_anchor
**bool**

Set to false, if you like to add your own anchor

	$config['toc_anchor']       = false;
	
**Note**

If you use `$config['toc_anchor'] = true;` then `{{ toc_top }}` will be disabled.

Screenshot
=============================================================================
![Screenshot of Table Of Contents](./Screenshot.png)
