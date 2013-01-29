<?php

class DataLinkerPlugin extends Omeka_Plugin_AbstractPlugin
{
	protected $_hooks = array('public_head',
                                'initialize');
	
	protected $_filters = array('record_metadata_elements',
#                                'admin_navigation_main',
#                                'public_navigation_main'
                            );

    /**
     * Initialize the plugin.
     */
    public function hookInitialize()
    {
#		print "1234";
        // Add the view helper directory to the stack.
    }

    /**
     * Add the data linker navigation link.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array('label' => __('Data linker'), 'uri' => url('data-linker'));
        return $nav;
    }

    public function hookPublicHead($args)
    {
        queue_css_file('linked'); // assumes myplugin has a /views/public/css/linked.css file
        add_filter(array('Display', 'Item', 'Dublin Core', 'Subject'), 'my_type_link_function');
        add_filter(array('Display', 'Item', 'Item Type Metadata', 'Text'), 'text_extreme_hide');
        add_filter(array('Display', 'Item', 'Item Type Metadata', 'Text'), 'text_author_hide');

    }
	
	public function filterRecordMetadataElements($elementSets)
	{
	}
    
    public function filterPublicNavigationMain($args){
        #ONLY FOR NAVIGATION
    }
    
}

#add_filter(array('Display', 'Item', 'Item Type Metadata', 'Text'), 'my_text_link_function');
#add_filter(array('Display', 'Item', 'Item Type Metadata', 'Text'), 'make_urls_clickable_in_text');
add_filter(array('Display', 'Item', 'Dublin Core', 'Source'), 'make_urls_clickable_in_text');

function make_urls_clickable_in_text($args){
	return url_to_link($args);
}

function my_type_link_function($args)
{
	if ($args){
		$btext = str_replace(" ", "+", $args);
		$type_information = get_type_info($args);
		$return_this = "<a class='hover-type' href='/omeka2/items/browse?search=&advanced%5B0%5D%5Belement_id%5D=51&advanced%5B0%5D%5Btype%5D=is+exactly&advanced%5B0%5D%5Bterms%5D=$btext'>$args<span class='classic'><br>$type_information</span></a>";
		return $return_this;
	}
	else{
		return false;
	}
}

function text_author_hide($args){
	if ($user = current_user()){
		return $args;
	}
	if ($args){
		if (metadata(get_current_record('item'), array('Dublin Core', 'Rights')) == "nee"){
			return "<b textcolor = 'red'>Auteursrecht:</b> De tekst bevat auteursrechtelijk beschermde informatie. De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut.
			<br>
			This text contains copyrighted information.";
		}
		else{
			return $args;
		}
	}
	else{
		return false;
	}
}


function text_extreme_hide($args){
	if ($user = current_user()){
		return $args;
	}
	if ($args){
		if (metadata(get_current_record('item'), array('Item Type Metadata', 'Extreme')) == "ja"){
			return "<b textcolor = 'red'>Extreme:</b> Dit record bevat extreme elementen van enigerlei aard (racisme, sexisme, schuttingtaal, godslastering, expliciete naaktheid, majesteitsschennis). De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut.
			<br>
			This text contains language that can be perceived as extreme.";
		}
		else{
			return $args;
		}
	}
	else{
		return false;
	}
}

/*
function my_text_link_function($text, $record, $elementText)
{
	$return_this = "<div class = 'story-text'>$text</div>";
#	$return_this = "<div class='bl'><div class='br'><div class='tl'><div class='tr'><div class = 'story-text'>$text</div></div></div></div></div><div class='clear'>&nbsp;</div>";
    return $return_this;
}
*/

function get_element_info_dynamic($search_string, $dublin_element_name, $show_elements_dublin = null, $show_elements_itemtype = null)
{
	if (!$show_elements_dublin || !$show_elements_itemtype){
		$show_elements = array("Title", "Creator", "Publisher");
		$show_elements = array("Subgenre");
	}
	$db = get_db();
	$sql = "
	SELECT items.id 
	FROM {$db->Item} items 
	JOIN {$db->ElementText} element_texts 
	ON items.id = element_texts.record_id 
	JOIN {$db->Element} elements 
	ON element_texts.element_id = elements.id 
	JOIN {$db->ElementSet} element_sets 
	ON elements.element_set_id = element_sets.id 
	WHERE element_sets.name = 'Dublin Core' 
	AND elements.name = '" . $element_name . "' 
	AND element_texts.text = ?";
	$itemIds = $db->fetchAll($sql, $search_string);
	print_r(get_class_methods($itemIds));
	
	if (count($itemIds) == 1){ //NOG EVEN MEE VERDER STOEIEN
		$temp_item = "";
		$found_item = get_record_by_id('item', $itemIds[0]["id"]);
		$temp_return = "<SPAN class='classic'>";
		foreach($show_elements_dublin as $show_element){
			$temp_return .= metadata($found_item, array('Dublin Core', $show_element)) . "<br>";
		}
		foreach($show_elements_itemtype as $show_element){
			$temp_return .= metadata($found_item, array('Item Type Metadata', $show_element)) . "<br>";
		}
		$temp_return .= "</SPAN>";
		return $temp_return;
	}
	return "no description";
}


function get_type_info($search_string)
{
	$db = get_db();
	$sql = "
	SELECT items.id 
	FROM {$db->Item} items 
	JOIN {$db->ElementText} element_texts 
	ON items.id = element_texts.record_id 
	JOIN {$db->Element} elements 
	ON element_texts.element_id = elements.id 
	JOIN {$db->ElementSet} element_sets 
	ON elements.element_set_id = element_sets.id 
	WHERE element_sets.name = 'Dublin Core' 
	AND elements.name = 'Identifier' 
	AND element_texts.text = ?";
	$itemIds = $db->fetchAll($sql, $search_string);
	print_r(get_class_methods($itemIds));
	
	if (count($itemIds) == 1){ //NOG EVEN MEE VERDER STOEIEN
		$temp_item = "";
		$found_item = get_record_by_id('item', $itemIds[0]["id"]);
		$temp_return = "<br><SPAN class='classic'>";
		$temp_return .= metadata($found_item, array('Dublin Core', 'Title')) . "<br>";
		$temp_return .= metadata($found_item, array('Dublin Core', 'Creator')) . "<br>";
		$temp_return .= metadata($found_item, array('Dublin Core', 'Publisher')) . "<br>";
		$temp_return .= metadata($found_item, array('Item Type Metadata', 'Subgenre')) . "<br>";
		$temp_return .= "</SPAN>";
		return $temp_return;
	}
	return "no description";
}

?>