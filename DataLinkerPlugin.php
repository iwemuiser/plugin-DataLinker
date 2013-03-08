<?php

class DataLinkerPlugin extends Omeka_Plugin_AbstractPlugin
{
	protected $_hooks = array('public_head',
                            'initialize');
	
	protected $_filters = array('record_metadata_elements',
	                            'file_markup',
	                            'item_citation'
#                                'admin_navigation_main',
#                                'public_navigation_main'
                            );

    /**
     * Initialize the plugin.
     */
    public function hookInitialize(){    }

    public function filterItemCitation($citation, $args) {
#        $citation = "None";
        return $citation;
    }

    public function filterFileMarkup($html, $args){
        if ($user = current_user()){
    		return $html;
    	}
        $file = $args['file'];
        if((metadata($file, array('Dublin Core', 'Rights')) == 'nee') || (metadata($file, array('Dublin Core', 'Relation')) == 'ja')){
            return '<p>Een bestand bevat auteursrechtelijke informatie of bevat extreme facetten. 
            <br>De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut, of met een admin account.<br><br></p>';
        }
        return $html;
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
#        add_filter(array('Display', 'Item', 'Item Type Metadata', 'Kloeke georeference'), 'my_kloeke_link_function', 4);
        add_filter(array('Display', 'Item', 'Item Type Metadata', 'Text'), 'text_extreme_hide', 5);
        add_filter(array('Display', 'Item', 'Item Type Metadata', 'Text'), 'text_author_hide', 6);
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
add_filter(array('Display', 'Item', 'Item Type Metadata', 'Kloeke georeference'), 'my_kloeke_link_function', 4);

        
function make_urls_clickable_in_text($args){
    return preg_replace('#(\A|[^=\]\'"a-zA-Z0-9])(http[s]?://(.+?)/[^()<>\s]+)#i', '\\1<a target="linked" href="\\2">\\2</a>', $args);
#	return url_to_link($args);
}

function my_kloeke_link_function($args){
    $kloeke_link = "http://www.meertens.knaw.nl/kaart/v3/rest/?type=dutchlanguagearea&data[]=$args";
    return "<a href='$kloeke_link'>$args</a>";
}


function my_type_link_function($args)
{
if ($args){
    $type_information = get_type_info($args);
    $search_url = url(array('module'=>'items','controller'=>'browse'), 
                    'default', 
                    array("search" => "",
                        "submit_search" => "Zoeken",
                        "advanced[0][element_id]" => "49",
                        "advanced[0][type]" => "is exactly",
                        "advanced[0][terms]" => "$args",
                        )
                    );
        $return_this = "<a class='hover-type' href='$search_url'>$args <span>$type_information</span></a>";
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
			return "<b textcolor = 'red'>Auteursrecht:</b> 
			De tekst bevat auteursrechtelijk beschermde informatie. 
			De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut.
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
			return "<b textcolor = 'red'>Extreme:</b> 
			Dit record bevat extreme elementen van enigerlei aard (racisme, sexisme, schuttingtaal, godslastering, expliciete naaktheid, majesteitsschennis). 
			De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut.
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
		$temp_return = metadata($found_item, array('Dublin Core', 'Title')) . "<br>";
		$temp_return .= metadata($found_item, array('Dublin Core', 'Creator')) . "<br>";
		$temp_return .= metadata($found_item, array('Dublin Core', 'Publisher')) . "<br>";
		$temp_return .= metadata($found_item, array('Item Type Metadata', 'Subgenre')) . "<br>";
		return $temp_return;
	}
	return "no description";
}

?>